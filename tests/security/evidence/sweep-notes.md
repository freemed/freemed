# Post-sweep module-smoke evidence — how the "0 deltas" claim is derived

Task 2.6e. This file and `module-smoke-after.json` exist so that the claim "the
2.6b SQL-concatenation sweep changed no module's behaviour" is **re-derivable
from the repository** instead of resting on report prose and scratch `/tmp`
files. Everything below is a command a reviewer can re-run.

## 1. What was swept (the three batch commit ranges)

The sweep is 70 commits on `security/mitigation-2026-09`, in three batches:

| batch | commit range | commits | scope |
|---|---|---|---|
| A | `b1603d5e..d219c7c4` | 17 | `lib/org/freemedsoftware/api/**`, `lib/org/freemedsoftware/core/**` |
| B | `d219c7c4..c14fa68a` | 33 | `lib/org/freemedsoftware/module/**` |
| C | `c14fa68a..0fa17e8c` | 20 | the remainder + the review follow-ups (F1–F7) |

(`git rev-list --count b1603d5e..d219c7c4` etc. give the counts above. The range
`b1603d5e~1..0fa17e8c` is 71 commits — one more than the batch counts sum to —
because it also contains `e3fee9ac`, the **immediate parent of batch A**
(`b1603d5e~1`): the Task 2.6d commit *fix: guard count() on a null summary
expression in EMRModule::qualified_query (PHP 8.3)* that re-baselined
`module-smoke-before.json`. **Corrected in fix round 1.** The paragraph that
previously stood here attributed the extra commit to `fa8c15b9` with the subject
*fix: guard count() on a NULL summary_query*. Both parts were wrong: `fa8c15b9` is
the 10th of batch A's own 17 commits in `git log --oneline b1603d5e..d219c7c4`, so
it is already counted inside the 70, and its real subject is *FormTemplate::
ProcessData: guard count() on a NULL summary_query (PHP 8.3 fatal)*.)

The harness run recorded here is at `HEAD = 0fa17e8c` (0fa17e8c689c566f6e331cef24176b9cd8989e1b),
i.e. after the last batch-C commit.

## 2. The exact command

The harness needs a database, and `php:8.3-cli` has no `mysqli`, so it is run in
the apache image against the **served snapshot** (the checkout deliberately has
no `lib/settings.php`; `sync-code.sh` copies the checkout into the scratch
docroot and restores the verification-only probe files):

```sh
cd /home/jbuchbinder/.hermes/cache/scratch/freemed-verify && ./sync-code.sh
docker compose -p freemed-verify exec -T web-apache php tests/security/module_smoke.php \
  > /home/jbuchbinder/Code/projects/freemed/tests/security/evidence/module-smoke-after.json
```

As executed, exit code was `0`; stdout (the artifact) is 10,055 bytes; stderr
(832 bytes) is only pre-existing PHP 8.3 deprecation noise from the loader
(`Using ${var} in strings is deprecated, use {$var} instead @ loader.php:57` and
friends), which the harness discards before printing its JSON. Nothing else was
written to stdout.

The registry was NOT re-registered for this run: `--register` writes to the
database and the registry (`modules` table, 135 rows) was already populated by
earlier Task 2.6d runs. The control row `__control_missing_module__` came back
`EXC: registry row does not resolve to a file (module_path='')`, which is the
harness's own proof that the run is not vacuous — had it come back `OK` the
harness would have exited 3 instead of 0.

## 3. Histogram

`module-smoke-after.json`, 136 entries:

| outcome | count |
|---|---|
| OK | 6 |
| EXC | 22 (21 modules + the `__control_missing_module__` control) |
| SKIP | 108 |
| **entries** | **136** |

## 4. Diff against the baseline

`module-smoke-before.json` (committed with Task 2.6d, the post-`count(null)`-guard
baseline) has the same histogram: **136 entries, OK 6, EXC 22, SKIP 108**.

The two files are **byte-identical**, so the entry-by-entry diff is empty by
construction — the "0 deltas" claim is exactly this:

```
$ sha256sum tests/security/evidence/module-smoke-before.json tests/security/evidence/module-smoke-after.json
8f46fb074dab3bf94e4cde628629e1bb5c69741aaf65036ecab75ff82b22bef1  tests/security/evidence/module-smoke-before.json
8f46fb074dab3bf94e4cde628629e1bb5c69741aaf65036ecab75ff82b22bef1  tests/security/evidence/module-smoke-after.json
```

One-line check (prints `byte-identical (0 deltas)` and exits 0 only if that is true):

```sh
cmp tests/security/evidence/module-smoke-before.json tests/security/evidence/module-smoke-after.json \
  && echo 'byte-identical (0 deltas)'
```

Equivalent, if you prefer to see the entry comparison rather than the file
comparison:

```sh
diff <(python3 -m json.tool tests/security/evidence/module-smoke-before.json) \
     <(python3 -m json.tool tests/security/evidence/module-smoke-after.json) && echo 'no entry differs'
```

No entry moved `OK -> EXC` and no entry moved `OK -> SKIP`; no new `EXC` message
appears. This run happens to be a *stronger* statement than "the EXC set did not
grow": it is the same bytes, so the OK set, the SKIP set and every message are
identical too.

## 5. Known coverage limit (read this before trusting the diff)

**The harness cannot see a valid-but-wrong query.** Most of the Phase 0.3 seed
tables are empty in the reduced scratch database, so a sweep edit that mangles a
`WHERE`/`ORDER BY` predicate usually does not fail — it returns **0 rows**, and 0
rows is recorded as `SKIP: GetList()/GetRecords() returned 0 rows, none with an
id`, which is exactly what an untouched module on an empty table also records.
**Corrected in fix round 1** — the split of the 108 `SKIP` entries (an earlier
version of this paragraph said *"108 of the 136 entries are SKIP for that reason"*)
is:

| SKIP entry text | count | is it the empty-table case? |
|---|---|---|
| `SKIP: GetRecords() returned 0 rows, none with an id` | 67 | yes |
| `SKIP: GetList() returned 0 rows, none with an id` | 29 | yes |
| `SKIP: GetAll+GetRecords() returned 0 rows, none with an id` | 6 | yes |
| `SKIP: GetAll+GetList() returned 0 rows, none with an id` | 1 | yes |
| `SKIP: no callable parameterless GetAll/GetRecords/GetList` | 4 | **no** — a different reason (no listing method the harness can call) |
| `SKIP: GetList() returned 1 rows, none with an id` | 1 | **no** — `PaymentModule`, one row present but carrying no id |

So **103** are the empty-table case, **4** are the no-callable-method case and
**1** (`PaymentModule`) records a row count. That last one is the only `SKIP`
entry a *row count* can move, and it is the one the `payrec` cascade in section 7
did move; if it is counted with the zero-row entries as *"no id came back, so this
harness cannot distinguish anything"*, the empty-table figure is 104. Derivation,
from the committed artifact:

```sh
python3 -c "import json,collections,re; d=json.load(open('tests/security/evidence/module-smoke-after.json')); \
s=[v for v in d.values() if v.startswith('SKIP')]; print(len(s)); \
[print(n,'|',k) for k,n in collections.Counter(re.sub(r'\s+',' ',v) for v in s).most_common()]"
108
67 | SKIP: GetRecords() returned 0 rows, none with an id
29 | SKIP: GetList() returned 0 rows, none with an id
6 | SKIP: GetAll+GetRecords() returned 0 rows, none with an id
4 | SKIP: no callable parameterless GetAll/GetRecords/GetList
1 | SKIP: GetList() returned 1 rows, none with an id
1 | SKIP: GetAll+GetList() returned 0 rows, none with an id
```

In other words:

* The pass criterion for this evidence is **"0 new `EXC`"** — no module moved
  from a working outcome to an exception. That is what was measured, and it is
  what the byte-identical diff establishes.
* It is **not** a statement that no swept query changed its result set. A module
  whose listing query was tightened or is now malformed-but-parseable will keep
  reporting `SKIP` and stay invisible to this diff.
* Coverage that does not depend on the table being empty is carried separately by
  the live relay checks and by
  `tests/security/repro-relay-sqli.sh`, which exercise real payloads against the
  served stack (see the task reports) rather than inferring from row counts.
* The 21 real `EXC` entries are pre-existing (missing tables / a `/tmp`
  `module_path` registry row / a `UserGroups` `TypeError`), not sweep
  regressions; they are byte-for-byte where they were before the sweep.

Raising the seed so the `SKIP` set shrinks is possible but is a seed/coverage
change to the harness's reference database, not an evidence-commit change: it
would move the baseline too, and this file claims nothing about it.

<!-- Sections 6-10 were added in Task 2.6e's fix round 1, after review found that
     the material below existed only in the gitignored .superpowers report. -->

## 6. Provenance of these two artifacts (generation-time binding)

Byte-identity is not provenance: two identical files are equally consistent with
*two runs* and with *one run copied twice*. What follows is the record that lets a
reader bind the bytes in the tree to a generation event, a harness revision and an
environment.

| item | value |
|---|---|
| `module-smoke-before.json` — commit that wrote the current bytes | `e3fee9ac` (Task 2.6d re-baseline); the file was first created in `567b9171` |
| `module-smoke-before.json` — generation-time `git rev-parse HEAD` | `e3fee9ac4ce27baabed9be90763194e98b1d4861` (the commit that recorded it — i.e. the pre-sweep state, the parent of batch A) |
| `module-smoke-after.json` + this file — commit | `3e6e790f` |
| `module-smoke-after.json` — generation-time HEAD | `0fa17e8c689c566f6e331fce24176b9cd8989e1b` (batch C, the sweep HEAD) |
| `sha256sum tests/security/module_smoke.php` | `fa789251844ba3539b52e9d961d8a61a65de5ff616f8fb55c86f7bcdf940b5c2` — unchanged since `567b9171`, so the *same* harness bytes produced both artifacts |
| `sha256sum tests/security/evidence/module-smoke-before.json` | `8f46fb074dab3bf94e4cde628629e1bb5c69741aaf65036ecab75ff82b22bef1` |
| `sha256sum tests/security/evidence/module-smoke-after.json` | `8f46fb074dab3bf94e4cde628629e1bb5c69741aaf65036ecab75ff82b22bef1` (identical, by design) |
| container | `freemed-verify-apache` (image `freemed-verify-php:apache`); DB `freemed-verify-db` (image `mariadb:10.11` = 10.11.19). `php:8.3-cli` has no `mysqli`, so the harness must run in the apache container |

The exact command (both runs):

```sh
cd /home/jbuchbinder/.hermes/cache/scratch/freemed-verify && ./sync-code.sh
docker compose -p freemed-verify exec -T web-apache php tests/security/module_smoke.php \
  > tests/security/evidence/module-smoke-after.json
```

Fix round 1 re-ran exactly that command **in the fix round's working tree**, not
against the frozen `997d98d9` commit: `997d98d9` (after Task 2.6f) was `HEAD` at
the time - it is the parent of this round's own first commit, measured with
`git rev-list --count 997d98d9..f74923e7` = 1 - while the round's edits were still
uncommitted; the same round then committed `f74923e7` (this file and the two seed
artifacts) and `57ec9cc3` (`EMRModule`'s identifier change). The output was
**byte-identical** to the committed `module-smoke-after.json` (`8f46fb07…` on
both; `cmp` exit 0, `exit=0` from `docker compose exec`, stderr
`module smoke: OK=6 EXC=22 SKIP=108 other=0 (registry rows=135)`). So the artifact
committed in `3e6e790f` is still the output of this harness for the tree this
round measured. **The staging itself is RECORDED, NOT RE-DERIVABLE:** no run logs
are committed, so a reader cannot reconstruct which file contents the served copy
held when the command ran; the round that made the run states it was the working
tree, and `doc/SECURITY_FOLLOWUP` §6.6 b) carries the same limit.

**What this record does and does not establish.** It establishes: the bytes in the
tree are this harness's stdout for the command above; the harness revision that
produced them is `fa789251…`; and the baseline's generation point is a specific
commit, not "somewhere before the sweep". It does **not** establish that the two
files came from two separate processes — nothing in a two-file repository can —
and, crucially, it does not establish that the two *databases* were identical. See
section 7.

## 7. Database incident during the after-run — read this before comparing the two files

**The after-run was not made against the database the baseline was recorded
against.** This material previously existed only in the gitignored
`.superpowers/sdd/2026-09-28_105726-freemed-security-mitigation/task-2.6e-report.md`;
a reader of the committed evidence could not know it.

1. To drive the live relay checks (task-2.6e report §3.4) I seeded `patient`,
   `procrec` and `vitals` rows, and loaded two missing schema functions
   (`TRANSLATE_CHARS`, `STRING_TO_PHONE`) into the scratch database.
2. Cleaning that up with `DELETE FROM patient WHERE id=1` **cascaded through
   `payrec_ibfk_1`** and deleted the pre-existing seeded `payrec` row. That was the
   one smoke delta the first post-fix comparison showed: `PaymentModule`
   `SKIP: GetList() returned 1 rows, none with an id` → `0 rows`. The cascade
   surface is wider than that one statement: `patient_emr_ibfk_1`,
   `payrec_ibfk_1`, `payrec_ibfk_2`, `procrec_ibfk_1` and `vitals_ibfk_1` all point
   at `patient`, so any `DELETE FROM patient` — and any `DELETE FROM procrec`,
   through `payrec_ibfk_2` — can silently take this row with it.
3. The row was restored **by hand, with an explicit id, under
   `SET FOREIGN_KEY_CHECKS=0`**:

   ```sql
   SET FOREIGN_KEY_CHECKS=0;
   INSERT INTO payrec (id, payrecpatient, payrecproc, payreccat, payrecsource,
                       payrecamt, payreclink, payrecdt, payreclock, user, active)
   VALUES (3,1,5,0,0,150.25,0,'2026-01-15','unlocked',1,'active');
   SET FOREIGN_KEY_CHECKS=1;
   ```

   **Correction (fix round 1).** The 2.6e report §5.5 described this as "the exact
   statement recorded in `task-2.2-2.5-report.md`". It was not. The statement
   recorded there (that report's line 76) is the *original* Task 2.5 seed and has
   **no explicit `id`**:

   ```sql
   INSERT INTO payrec (payrecpatient, payrecproc, payreccat, payrecsource,
                       payrecamt, payreclink, payrecdt, payreclock, user, active)
   VALUES (1,5,0,0,150.25,0,'2026-01-15','unlocked',1,'active');
   ```

   What was actually run during the after-run is the first block above: the same
   values **plus `id 3`** and the `FOREIGN_KEY_CHECKS=0` wrapper, which is what
   keeps the restored row off a new auto-increment id.
4. Consequences the restore left behind, recorded so a reader is not surprised:
   the `payrec` row is id 3 again; its `patient_emr` companion — written
   automatically by `payrec_Insert` (AFTER INSERT) — is now **id 5** (it was id 3)
   with `stamp = NOW()` at restore time; the extra `payrec` row that my `procrec`
   seed had created through `procrec_Insert` was deleted; and `patient_emr`'s
   AUTO_INCREMENT counter is left wherever those runs put it (which is why
   `seed-verify.sql` pins the companion with an `UPDATE`, never with a counter).
5. **What this means for the diff.** Byte-identity between the two artifacts was
   reached only **after** that repair. The honest reading is *"a re-run taken after
   restoring the row reproduced the pre-sweep baseline byte for byte"*, **not**
   *"two runs against identical databases reproduced each other"*. The two files
   must therefore be read together with `seed-fingerprint.md` (section 9): the only
   entry in all 136 that responds to the damage is `PaymentModule`, i.e. the very
   entry the comparison has to trust.
6. The repair was complete in every other observable: `patient`/`procrec`/`vitals`
   0 rows, `modules` 135, `entemplate` 1, `payrec` 1, `patient_emr` 1, `user` 2,
   and the two loaded functions dropped. No other seeded row was lost. The
   database has no binlog, so the diagnosis was by exclusion — `PaymentModule` was
   the only entry that ever differed in the smoke.
7. The same hazard applies to any future live probe on this database:
   **do not seed a `patient` row to make a probe work**, and if you must seed
   `vitals`/`procrec`, delete those rows by their own id afterwards rather than
   deleting the `patient` row they point at. `seed-fingerprint.md` records the
   counts a probe must leave behind.

## 8. One-line instruments that existed only in the served snapshot

The live checks in task 2.6e needed the *statement the code assembles*, not just
the response body, and for two of the three methods the relay cannot show it (its
body is empty or fatal before the query runs — section 10). The served snapshot
`/home/jbuchbinder/.hermes/cache/scratch/freemed-verify/app` therefore carried
three one-line instruments for the duration of those checks:

* `$result = $query;` in `PaymentModule::GetLedger`, in place of the
  `$sql->queryAll($query)` call (the method fatals on an undefined `$sql`);
* `file_put_contents('/tmp/sqlcap.txt', …)` immediately after each of the two
  `EMRModule` query assemblies, so the emitted statement survives the process dying
  at its Smarty step.

They never entered the repository, and the served copy was re-synced afterwards
(`./sync-code.sh`; `diff -q` clean for both files against the checkout, and the
final byte-identical smoke was taken from the re-synced copy).

**The consequence is a real gap:** the emitted-statement tables in the task-2.6e
report **cannot be re-derived from this repository today**. Only the consequences
that a relay body can show (section 10) are reproducible from the tree.

Fix round 1 added nothing to that debt. Its new measurements of the same two
`EMRModule` sites were taken with **no instrument in the served code at all**, from
the database server's own general log:

```sh
docker exec freemed-verify-db mysql -ufreemed -pfreemed \
  -e "SET GLOBAL general_log_file='/tmp/glog-fix1.sql'; SET GLOBAL general_log=ON;"
#   … drive the probe (relay.php, or the in-process probe) …
docker exec freemed-verify-db mysql -ufreemed -pfreemed -e "SET GLOBAL general_log=OFF;"
docker exec freemed-verify-db grep -n 'lock_count\|FROM .vitals.' /tmp/glog-fix1.sql
```

That recipe is in the tree, so those measurements *are* reproducible; the older
ones are not.

## 9. Seed drift, and the fingerprint check

The seed state the baseline was recorded against is **not reconstructible from the
seed script that was recorded at the time** (that report's line 72–79): it inserts
`payrec` **without an explicit id** and says nothing about the `patient_emr`
companion. Re-running it today can therefore produce a different id, or **two**
`payrec` rows (`procrec_Insert` inserts a `payrec` row of its own for every
`procrec` row), or **zero** (seed a `patient` row, then delete it — section 7).
Because the smoke *does* observe row counts — `PaymentModule` records
`1 rows`/`0 rows` — any of those turns into a spurious delta against the committed
baseline, and the delta looks exactly like a sweep regression.

This commit adds:

* **`seed-verify.sql`** — the deterministic reduced seed. Explicit id 1 for
  `entemplate`, explicit id 3 for `payrec`, explicit id 5 for the `patient_emr`
  companion (pinned with `UPDATE … WHERE id <> 5`, because the `payrec_Insert`
  trigger writes that row and hand-inserting it would leave two), and explicit
  `module_uid` for the two `modules` registry rows that
  `module_smoke.php --register` does *not* produce: `11111111-…` (`DemoPayments`,
  `module_path='/tmp'`, the does-not-resolve control) and `709a8d12-…` (the
  `entemplate`-backed duplicate `EncounterNotesTemplate` class, which is what makes
  the `EncounterNotesTemplate#2` entry exist). The file is idempotent
  (`ON DUPLICATE KEY UPDATE`), never deletes anything, and never touches an
  AUTO_INCREMENT counter. The 133 remaining registry rows come from `--register`
  (deterministic `module_uid = md5(module_class)`; only `module_stamp` varies, and
  no smoke outcome reads it): 133 + 2 = the 135 rows the baseline was recorded
  against.
* **`seed-fingerprint.md`** — the row counts, ids and per-table role the baseline
  was recorded against, the canonical fingerprint query and its recorded output,
  the check procedure, and what each kind of mismatch means.
* The **procedure**, in order: `--register` once on a fresh database → apply
  `seed-verify.sql` → run the fingerprint query and compare it → *then* compare the
  two artifacts. Checking the artifacts first is what produced the false alarm in
  section 7.

Applying `seed-verify.sql` to the current database and re-running the harness
produced **the committed `module-smoke-after.json` byte for byte** (`8f46fb07…`,
`cmp` exit 0), i.e. the seed file reproduces the environment of the committed
artifact and no observable moved — so this commit does not need to re-generate
`module-smoke-after.json`, and `module-smoke-before.json` is untouched. The file
was also exercised from an empty state (inside a rolled-back transaction, no
committed change): `entemplate` id 1, `payrec` id 3, `patient_emr` id 5 and both
hand-written registry rows come out exactly as recorded.

## 10. Methods that were INCONCLUSIVE at the relay, and why

"INCONCLUSIVE" means the relay returned HTTP 200 with a body that cannot decide the
question — not that the check was skipped, and not that it passed. The three
methods touched by task 2.6e, with the reason measured on this stack:

| method | line (2.6e pre-fix → as of this commit, `997d98d9`) | verdict | reason |
|---|---|---|---|
| `EMRModule::locked()` | `:295` → `:299` | **PASS** | real query, real row: the relay body is `true` for a seeded row with `locked > 0`, `false` for a non-existent id (negative control), and hostile input collapses to the same numeric prefix. Asserted on bodies. |
| `EMRModule::RenderHtmlView()` | `:946` → `:973` | **INCONCLUSIVE** | the relay answers 200 with a **0-byte** body for benign and hostile input alike, because the method dies at its own Smarty initialisation (`Undefined property: Vitals::$smarty`; that read is at `EMRModule.class.php:994` on the tree this table's line numbers are quoted from, `997d98d9` — `:1018` after the fix round's own edit, `:967` when the report was written) before it prints — a pre-existing condition, identical before and after the change. A 0-byte body carries no evidence either way. |
| `PaymentModule::GetLedger()` | `:1181` → `:1206` | **INCONCLUSIVE** | the method fatals before any SQL runs (`Call to a member function queryAll() on null`, undefined local `$sql`); and on MariaDB the assembled statement is invalid in **every** branch anyway (`1 == 1`, and the misspelled columns `c.cptname`, `c.cptnameint`, `pr.procdtbileld`). Nothing about it can be positively exercised through the relay. |
| `EMRModule::locked()` with a hostile id, `RenderHtmlView()` with a hostile id | — | **PASS for the payload itself** | neither body ever contained the seeded MD5 digest `[REDACTED]`, the token `userpassword`, or MySQL error text. |

**Redaction note, 2026-09-28 (final fix wave).** The row above used to print the
harness `admin` account's password MD5 digest verbatim. It was the only
credential-equivalent string in the whole `79efd68d..HEAD` diff (review R27, and
`doc/SECURITY_ADVISORY` §7.C item 8 gates publication on removing it), and it is
now `[REDACTED]`: the sentence's meaning - *neither hostile payload's body carried
the seeded digest, the token or MySQL error text* - is unchanged, and the digest
itself is recoverable by nobody from this file. That replacement is the only edit
this file took for R27.

The two INCONCLUSIVE rows are the reason the emitted statements had to be captured
(section 8), and the reason the notes state the pass criterion as "0 new `EXC`"
rather than "the queries were proven equivalent". The measures that *are*
reproducible from the tree today are the relay bodies and
`tests/security/repro-relay-sqli.sh`, not the statement captures.

## 11. Value-shape guards this evidence records from the code (ledger item 22)

Recorded here in the final fix wave because it lived only in the sweep's own
gitignored report (`.superpowers/`) and in code comments, with no tracked evidence
entry.

`module/PaymentModule.class.php`'s `GetLedger()` assembles its view predicate from a
LOCAL `$view_query`, and the sweep's fix round left exactly one point of assembly at
`:1181-1183` (the block the round's reports cite as `:1178-1183`; its rationale
comment is `:1177-1180`):

```php
$view_query = '';
switch ($type) {
    case 'closed':    $view_query = "procbalcurrent = '0'";  break;
    case 'nonclosed': $view_query = "procbalcurrent !='0'";  break;
    case 'unpaid':    $view_unpaid = "procbalcurrent >'0'";  break;   // <- pre-existing typo, see :1163-1169
    case 'all':
    default:          $view_query = "1 == 1";                break;
}
if (!in_array($view_query, array(
    "procbalcurrent = '0'", "procbalcurrent !='0'", "1 == 1", ''
), true)) { $view_query = ''; }
```

The whitelist mirrors the switch LITERALLY - the three assigned literals plus the
empty fragment the `unpaid` branch leaves in place, because that branch's assignment
writes the mistyped `$view_unpaid` (a pre-existing functional bug, recorded at
`:1163-1169`, NOT repaired here because repairing it would change which rows come
back). It fails CLOSED to `''`, so a later edit that routed caller data into the
fragment would be dropped rather than spliced into the statement below it. The three
literals are also carried by the generated
`tests/security/evidence/identifier-inventory.txt` (`$view_query: clause-fragment=3`),
whose `SqlIdent` cross-check runs in `tests/security/sql_ident.test.php`; the
statement itself is one of the INCONCLUSIVE relay methods in section 10 above.

