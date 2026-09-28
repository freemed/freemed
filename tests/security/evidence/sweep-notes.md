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
`b1603d5e~1..0fa17e8c` is 71 commits: it also contains the non-sweep Task 2.6d
commit `fa8c15b9` *fix: guard count() on a NULL summary_query*, which is why the
batch counts sum to 70 and not 71.)

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
108 of the 136 entries are `SKIP` for that reason. In other words:

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
