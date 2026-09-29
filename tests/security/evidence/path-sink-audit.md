# path-sink-audit.md — sibling PATH_INFO / file-serving sinks (Task 1.3)

**Scope:** does any sibling of the `help.php` sink (Phase 1, CWE-22) have the same
shape — *validate some parameters, not the tail, then hand the tail to the
filesystem*?

**Measured on:** branch `security/mitigation-2026-09`.
The chtml.php code change was made on top of `57ec9cc3`; every "before" row below
was measured with the served tree at `57ec9cc3` and every "after" row with the
served tree at the same commit plus this task's working-tree change.

| | |
|---|---|
| Server A | Apache/2.4.68 + mod_php 8.3.35, `http://localhost:38081/`, docroot `/var/www/html` (= `$VERIFY/app`), `AcceptPathInfo On`, `AllowEncodedSlashes Off` |
| Server B | nginx/1.31.6 + php-fpm 8.3.35, `http://localhost:38082/`, docroot `/usr/share/freemed` (same `$VERIFY/app`) |
| DB | MariaDB 10.11.19 at `127.0.0.1:33306` (216 tables) |
| Harness | `/home/jbuchbinder/.hermes/cache/scratch/freemed-verify`, re-synced with `./sync-code.sh` before every measurement |
| Client | `curl --path-as-is` for every traversal row (without it curl collapses dot segments client-side and the row measures nothing) |

Nothing in this file was inferred from another file's behaviour: every claim has
a command next to it, and the rows that were *not* measured say so.

---

## 1. The sink the brief names: `chtml.php`

```
chtml.php:30   $parts = explode ( '/', $_SERVER['PATH_INFO'] );
chtml.php:31   $file  = $parts[1];                                   <- unvalidated
chtml.php:34   if ( !file_exists( dirname(__FILE__)."/doc/${file}.chtml" ) ) { ... }
chtml.php:40   $chtml = CreateObject ( "org.freemedsoftware.core.CHTMLReader", ..."/doc/${file}.chtml" );
chtml.php:47   print $chtml->GetResource ( $path );                   <- $path, inside the tarball
```

### 1.1 Anonymity — measured (this is the finding)

No session cookie is sent, and none is required:

```
$ curl -s -D- -o- 'http://localhost:38081/chtml.php/main' | head -1
HTTP/1.1 200 OK

$ curl -s 'http://localhost:38081/chtml.php/main'
Help index main not present.

$ curl -s 'http://localhost:38081/chtml.php/nosuchpage.zzz'
Help index nosuchpage.zzz not present.
```

Same on the nginx port (38082). **HTTP 200 with no session** — so if a
`doc/<name>.chtml` exists, its content is anonymous.

The shipped tree contains **zero** `doc/*.chtml` archives (CHTML is a `.tar.gz`
with the extension changed — `doc/CHTML_FORMAT`), and none has ever been added to
this repository in any reachable ref:

```
$ find /home/jbuchbinder/Code/projects/freemed -name '*.chtml' | wc -l
0
$ git log --all --oneline --diff-filter=A --name-only -- '*.chtml'
(no output)
```

So on a stock deployment this endpoint discloses the string
`Help index <token> not present.` and nothing else. To measure what it *would*
disclose, a throwaway archive was installed **in the served copy only**
(`$VERIFY/app/doc/zz21probe.chtml` — never in the checkout, removed afterwards):

```
$ curl -s 'http://localhost:38081/chtml.php/zz21probe/index.html'     # NO cookie
HTTP 200, body(23 bytes): CHTML-PROBE-FIXTURE-OK
```

**Verdict: reachable-from-input? YES, anonymously. confined? YES for traversal —
but the endpoint is unauthenticated and will serve whatever `doc/*.chtml`
exists.** Recommendation, one line as required:

> **consider the same `LoggedIn` gate as `help.php`, decided by the deployment
> owner.**

(Not added by this task: the brief makes it conditional and an outward-facing
behaviour change is outside this mitigation — see §5.3 of task-1.3-report.md.)

### 1.2 Why the sink was survivable, and what it was not

Both facts below were verified by reading the code, not assumed:

1. **Forced suffix** — the token is interpolated as `"/doc/${file}.chtml"`, so a
   traversal target must itself end in `.chtml`, and `file_exists()` gates it.
2. **The token is never a filesystem path after the gate** —
   `CHTMLReader::GetResource($path)` runs
   `passthru("tar Oxzf ".escapeshellarg($this->file)." ".escapeshellarg($resource)." ...")`
   (`CHTMLReader.class.php:63`): `$path` is a member name *inside* the archive and
   is `escapeshellarg`-quoted. No command injection, no path traversal there.

What was missing is validation: an unvalidated token reached `file_exists()` and
the constructor.

### 1.3 Hostile shapes — measured before and after

> **Fix round 1 (post-review).** The token class is now anchored at the true end
> of the subject — `preg_match('/^[A-Za-z0-9._-]+\z/', $file)` — because PCRE
> `$` also matches immediately BEFORE a final newline. The predicate quoted
> further down this section accepted `"abc\n"`, and Apache handed PHP exactly
> that: `GET /chtml.php/abc%0A` was answered `200 Help index abc\n not present.`
> (nginx strips the byte upstream, so it was Apache-only in delivery). The gate
> now carries 9 hostile rows — both newline tokens added, with their own verdict
> rule — plus a `--self-test`, and it refuses to report PASS without a measured
> legitimate-page control. The run summaries, the token table and the predicate
> listing below are the previous round's 7-row measurements; treat them as the
> pre-fix baseline, with the current measured output in `task-1.3-report.md` §6.

`tests/security/repro-chtml-path.sh` (new, this task) is the executable form of
the requirement. Pre-change run against Apache:

```
7 hostile row(s): 0 rejected by PHP, 2 reached PHP unvalidated, 0 leaked, 5 refused upstream
>>> RESULT: FAILED - 2 hostile row(s) unvalidated, legitimate-page control served
```

| token after `/chtml.php/` | Apache: PHP sees | pre-change | post-change |
|---|---|---|---|
| `..%2f..%2f..%2fetc%2fpasswd` | — (404 upstream, `AllowEncodedSlashes Off`) | refused upstream | refused upstream |
| `..%2fzzchtmlprobe` | — (404 upstream) | refused upstream | refused upstream |
| `%2e%2e%2fzzchtmlprobe` | — (404 upstream) | refused upstream | refused upstream |
| `%252e%252e%2fzzchtmlprobe` | `/.../%2e%2e%2fzzchtmlprobe` | **200 `Help index %2e%2e not present.`** | **400 `Invalid CHTML page name.`** |
| `..%5Czzchtmlprobe` (backslash) | `/..\zzchtmlprobe` | **200 `Help index ..\zzchtmlprobe not present.`** | **400 `Invalid CHTML page name.`** |
| `zzchtmlprobe%2500` (literal `%00`) | `/zzchtmlprobe%00` | **200 `Help index zzchtmlprobe%00 not present.`** | **400 `Invalid CHTML page name.`** |
| `../../../../etc/passwd` | — (400 upstream, escapes root) | refused upstream | refused upstream |

nginx (38082) is the same, except it also hands `%252e%252e%2f…` through
(3 rows reached PHP unvalidated pre-change, 3 rejected post-change).

Post-change, both servers:

```
== hostile tokens (no cookie, --path-as-is) ==
7 hostile row(s): 2 rejected by PHP, 0 reached PHP unvalidated, 0 leaked, 5 refused upstream   [Apache]
7 hostile row(s): 3 rejected by PHP, 0 reached PHP unvalidated, 0 leaked, 4 refused upstream   [nginx]
legitimate page control: served
>>> RESULT: PASS
```

**Honest statement about `..%2f` and `../`:** a *single*-encoded separator never
reaches PHP on this stack — both servers normalise or refuse it first (this
matches verify-env.md §6 D-a, and was re-measured here:

```
$ curl -s --path-as-is 'http://localhost:38081/pathinfo-probe.php/%252e%252e%252fRelay/x' | grep -o 'PATH_INFO=[^ ]*'
PATH_INFO=/%2e%2e%2fRelay/x
```

— i.e. PATH_INFO is single-decoded and a literal escaped separator survives into
`$file` as text). So the rejection of `..%2f`/`../` was demonstrated at two
layers, and they are labelled as such:

* **at the deployment layer** — the request is refused upstream (400/404) before
  PHP runs (rows above);
* **at the validation layer** — the shapes that *do* reach PHP carrying a
  separator/dot-segment/NUL (`%2e%2e%2f…`, `..\…`, `…%00`) are rejected by the new
  token check (rows above), and the predicate itself rejects every shape the
  server normalises away:

```
$ docker run --rm php:8.3-cli php -r '...preg_match("/^[A-Za-z0-9._-]+$/",$t)...'
'..'             => ACCEPTED by the regex
'../'            => REJECTED by the regex
'..%2f'          => REJECTED by the regex
'%00'            => REJECTED by the regex
'%2e%2e%2fmain'  => REJECTED by the regex
'..\\x'          => REJECTED by the regex
'zz%2500'        => REJECTED by the regex
'dojo_en_US'     => ACCEPTED by the regex
'main'           => ACCEPTED by the regex
"a\0b" (raw NUL) => REJECTED by the regex
```

Bare `..` is accepted by the brief's token class (the class includes `.`). It is
**inert and, on this stack, unreachable**: no separator can be admitted, so the
composed path is `doc/...chtml`, and `/chtml.php/..` is normalised to `/` by both
servers (measured: HTTP 302 to the index redirect, PHP never runs). Recorded as a
known residual rather than papered over; the sibling resolver in
`lib/help-path.php:38` additionally rejects any token containing `..`, which is
the precedent if this is ever tightened.

### 1.4 The change made

```php
-$file = $parts[1];
+$file = isset ( $parts[1] ) ? $parts[1] : '';
+
+if ( ! preg_match ( '/^[A-Za-z0-9._-]+$/', $file ) ) {
+	Header ( 'HTTP/1.1 400 Bad Request' );
+	print 'Invalid CHTML page name.';
+	exit;
+}
```

* `isset()` — without it `/chtml.php` (no PATH_INFO) raised
  `Warning: Undefined array key 1` on PHP 8.3 for every such request.
* The regex is exactly the brief's. No `LoggedIn` gate (see §1.1).
* Behaviour for valid tokens is unchanged: the Dojo UI's own form
  (`ui/dojo/view/org.freemedsoftware.ui.chtmlbrowser.tpl:99`) is
  `/chtml.php/dojo_<locale>/<topic>.html`, i.e. `$file = 'dojo_en_US'`, which the
  class accepts. Measured after the change:
  `GET /chtml.php/zzchtmlprobe/index.html` (installed archive) → 200 + content,
  `GET /chtml.php/dojo_en_US/topic.html` → 200 `Help index dojo_en_US not present.`
* Behaviour that *does* change, stated explicitly: an invalid token now answers
  `400 Invalid CHTML page name.` instead of `200 Help index <junk> not present.`,
  and a request with no PATH_INFO at all (`/chtml.php`, `/chtml.php/`) now answers
  400 instead of echoing `Help index  not present.`.

`php -l` (see task-1.3-report.md for the verbatim run) reports
`No syntax errors detected in chtml.php`; the three `${var}` deprecation notices
it prints are pre-existing (they also print when linting `HEAD:chtml.php`).

---

## 2. Every hit of the brief's two greps, with a verdict

Legend: **R?** = reachable from request input. **C?** = confined (the value is
either not a filesystem path, or a component that input cannot influence/escape).
Rows marked *(vendored)* are third-party code the brief's `grep -v pear` does
**not** exclude; they are listed because the brief asks for every hit, and the
verdict says why they carry no request path in this tree.

### 2.1 Grep 1 — `grep -rn "PATH_INFO" --include=*.php . | grep -v '^./lib/net/php/pear'`

| hit | R? | C? | verdict |
|---|---|---|---|
| `install.php:49` | YES | n/a | Not a filesystem sink: the value flows into `$smarty->assign('base_uri', ...)` for the installer template only. It is `dirname(str_replace(PATH_INFO,'',REQUEST_URI))`. No path op on it anywhere. |
| `help.php:32` | YES | n/a | `syslog()` of the denied request (Phase 1). Log line only. |
| `help.php:38` | YES | YES | Fixed: goes through `help_resolve_path()` (lib/help-path.php), which validates ui/locale/page and `realpath()`-confines to the help dir. |
| `help.php:41` | YES | n/a | `syslog()` of the rejected path. Log line only. |
| `relay.php:37` | YES | n/a | `SystemLog()` of the request. Log line only. |
| `relay.php:42` | YES | PART | `explode('/')` → provider (`$parts[1]`), method (`$parts[2]`). Provider: `preg_match('/^[[:alpha:]]+$/')` at :46 **then** `file_exists("Relay_{$provider}.class.php")` — the only file the sink can compose is `lib/org/freemedsoftware/core/Relay_<alpha>.class.php`. Method is unvalidated but is only a `CallMethod` name, never a path. Measured: `/relay.php/json/..%2f..%2fetc%2fpasswd/x` → 404 (Apache, upstream) / 404 (nginx); `/relay.php/json/%252e%252e%252fRelay/x` → PATH_INFO `/json/%2e%2e%2fRelay/x`, so `$provider='Json'` (alpha, real provider) and the dot-segment lands in the *method* position → `INVALID_SESSION`. No traversal. |
| `ui/dojo/class.Controller.php:38` | YES | n/a | Same `base_uri` pattern as install.php, display only. |
| `controller.php:32` | YES | n/a | `SystemLog()` of the request. Log line only. |
| `controller.php:37` | YES | YES | **Closest sibling of help.php** (`$layout` validated alpha at :43, `$piece` **not** validated) but the file sink is unreachable on this stack: `controller.php:48` interpolates `${layout}` and PHP 8.3 fatals there for *every* layout — measured `/controller.php/Dojo/default` → `Fatal error: Uncaught Error: Undefined constant "layout" in controller.php:48`, before either `include_once` at :54/:68. Where that fatal is absent (PHP ≤ 8.1) the confinement is the forced `controller.` prefix + `.php` suffix + `file_exists()` on the same composed path, and the alpha-only layout. Non-alpha layout is rejected: `/controller.php/DOjo!/x` → `Hack attempt, dying ( 'Dojo!' given ).` |
| `tests/security/help_path.test.php:191` | — | — | Test fixture string (Phase 1's unit test). |
| `chtml.php:30` | YES | **was NO** | The sink this task fixes; see §1. |
| `chtml.php:46` | YES | YES | `$path` = PATH_INFO with the leading `/<file>` removed; used as a *member name inside the tarball* (`GetResource` → `tar Oxzf` + `escapeshellarg`). Not a filesystem path. |
| `lib/help-path.php:7`, `:13` | — | — | Doc comments. |
| `lib/org/freemedsoftware/core/Relay.class.php:33` | YES | n/a | `$this->query_string = $_SERVER['PATH_INFO']` — dispatch/log state. Line 34 is `file_get_contents('php://input')`, a stream wrapper, not a path. |

### 2.2 Grep 2 — `grep -rn 'readfile\|file_get_contents\|include *( *\$' --include=*.php ./lib/org/freemedsoftware ./ | grep -v pear`

67 unique hits (108 raw lines: the two tree arguments duplicate each other).
**Gap in the brief's pattern, reported:** `include *( *\$` requires `(` immediately
after `include`, so it matches `include($x)` but **not** `include_once(`,
`require_once(` or `include(dirname(...)...)`. A second pass is in §2.3.

| hit | R? | C? | verdict |
|---|---|---|---|
| `data/fpdf-fonts/makefont/makefont.php:71` |  |  | *(vendored)* FPDF font builder; `$file` from argv. CLI tool, not served. |
| `help.php:47` | YES | YES | `readfile($fullpath)` on the resolver's `realpath()`-confined result (Phase 1). |
| `lib/agata7/classes/fpdf151/fpdf.php:479`, `:554` |  |  | *(vendored)* legacy FPDF `include($file)`; font/metafile names are code-supplied inside the vendored library. No request path in this tree. |
| `lib/agata7/classes/pclzip/parser.php:45` |  |  | *(vendored)* `file_get_contents($prefix.'/content.xml')`, internal zip temp. |
| `lib/agata7/classes/phpdocwriter/.../pdw_base.php:195` (commented), `:196`, `:306` |  |  | *(vendored)* phpdocwriter temp dir (`pdw_tmpdir.$this->tmpdir.$this->filename`), library-internal. |
| `lib/agata7/classes/phpdocwriter/.../pdw_document.php:1131` |  |  | *(vendored)* image file inside the writer's own doc dir. |
| `lib/net/php/smarty/plugins/function.config_load.php:109` | MAYBE | PART | *(vendored)* Smarty: `include($_compile_file)` of the *compiled* config path. Reachable only through a template's `{config_load}`; the template name is code-authored (see `TeX::RenderFromTemplate` in §3.2 for the one caller that passes a request-derived name — recorded as an observation, not measured). |
| `lib/net/php/smarty/plugins/function.fetch.php:210` | MAYBE | PART | *(vendored)* `{fetch file=...}` — the file name comes from the template, templates are code-authored. |
| `lib/net/php/smarty/sysplugins/smarty_internal_cacheresource_file.php:232`, `smarty_internal_config.php:160`, `:233`, `smarty_internal_resource_file.php:65`, `smarty_internal_resource_php.php:116`, `smarty_internal_templatebase.php:156`, `:166`, `:170` | MAYBE | PART | *(vendored)* Smarty resource layer: every target is a path Smarty built from the template name passed to `display()/fetch()` plus the configured template/compile/cache dirs. No `display()/fetch()` call site in this tree passes `$_REQUEST`/`$_GET`/`$_POST` as the template name (checked). |
| `lib/org/freemedsoftware/acl/adodb/adodb-xmlschema.inc.php:1760`, `:1799`, `:1810`, `adodb-xmlschema03.inc.php:1927`, `:1966`, `:1977` |  |  | *(vendored)* ADOdb XML-schema loader; `$filename`/`$schema`/`$xsl_file` are schema files named by the ACL setup code. |
| `lib/org/freemedsoftware/acl/adodb/drivers/adodb-sqlite3.inc.php:697` |  |  | *(vendored)* `file_get_contents($path)` = the sqlite DB path from the connection config. |
| `lib/org/freemedsoftware/api/ModuleInterface.class.php:278` | NO | YES | `print file_get_contents($thisFile)`; `$thisFile = module_function($r[0]['module_namespace'],'RenderToPDF',array($r[0]['oid']))` — the oid is `(int)`-cast by the caller and the namespace comes from the `modules` table. Path produced by the module, not by input. |
| `…/ModuleInterface.class.php:290` | NO | YES | Same, via `MultiplePDF::Composite()` temp file. |
| `lib/org/freemedsoftware/api/Remitt.class.php:1305` | PART | YES | `file_put_contents($cached_name, file_get_contents($url))` — `$url` is the configured WSDL endpoint, `$cached_name` a `data/cache` name. Remote-fetch (SSRF-class at most), not a path-traversal sink. |
| `lib/org/freemedsoftware/api/Signatures.class.php:286` | PART | YES | Same shape as Remitt:1305. |
| `lib/org/freemedsoftware/core/BaseModule.class.php:209` | PART | YES (as measured) | `readfile($file)` where `$file` is either the module's TeX/`MultiplePDF` render temp or `$_file` (both server-generated). The *request* value that reaches a path is `$_REQUEST['print_template']` — see §3.2, an unmeasured observation. |
| `lib/org/freemedsoftware/core/Djvu.class.php:130`, `:181`, `:215` | NO | YES | `readfile($cache_name)`; `$cache_name = PHYSICAL_LOCATION.'/data/cache/djvu/'.$this->md5.'.'.$page.(...).'jpg'`. `md5` and page number, no input. |
| `…/Djvu.class.php:298` | NO | YES | `readfile($t.'.pdf')` — `$t` is this method's own temp basename. |
| `lib/org/freemedsoftware/core/EMRModule.class.php:1058` | NO | YES | `readfile($file)` = `$this->RenderToPDF($id)` output (temp), guarded by `file_exists`. |
| `lib/org/freemedsoftware/core/ExternalPlugin.class.php:162` | NO | YES | `readfile($this->cache)`; `$this->cache = .../data/cache/'.strtolower(get_class($this))`. No in-tree instantiation of `ExternalPlugin` was found (`grep -rn ExternalPlugin` → class definition only). |
| `lib/org/freemedsoftware/core/FPDF.class.php:1144`, `:1224`, `:1539` |  | YES | *(vendored)* FPDF: `include($this->fontpath.$font)` / image / font file read, names supplied by the library's own API callers in-tree. |
| `lib/org/freemedsoftware/core/LanguageRegistry.class.php:115` | NO | YES | `file_get_contents('./locale/'.$dir.'/registry')`; `$dir` comes from a `readdir('./locale')` listing filtered to directories (`:53`), i.e. server-side names. |
| `lib/org/freemedsoftware/core/PatientDataStore.class.php:199` | PART | YES | `file_get_contents($contents)` — `StoreFile()`'s 4th argument; every in-tree caller passes a temp/upload path (`tempnam`, `move_uploaded_file` target, `GetLocalCachedFile()`). |
| `…/PatientDataStore.class.php:233` | NO | YES | `file_get_contents($hash)` = `PHYSICAL_LOCATION.'/data/cache/'.$table_name.'-'.md5($id)`. |
| `lib/org/freemedsoftware/core/PDF.class.php:92` | NO | YES | `readfile($tmp)`; `CachedPageName($page) = cache_dir.$page.'.png'` with `$page` rejected unless numeric at `:81-83`. |
| `lib/org/freemedsoftware/core/Relay.class.php:34` | YES | n/a | `file_get_contents('php://input')` — stream wrapper, not a path. |
| `lib/org/freemedsoftware/module/DicomModule.class.php:288` | NO | YES | `readfile($temp)`, `$temp = tempnam('/tmp','dicomView')`. |
| `…/DicomModule.class.php:294` | PART | YES | `readfile($pic)`; `$pic = $pds->ResolveFilename($patient+0, get_class($this), $id+0)` → `base_path/<patient path>/<module>/<id>` with integer ids. |
| `…/PhotographicIdentification.class.php:253` | NO | YES | `readfile($x)` where `$x` is a literal path constant (`ui/dojo/htdocs/images/teak/noimage.250x250.png`). |
| `…/PhotographicIdentification.class.php:334` | PART | YES | `readfile($djvu->GetPage…)` → the Djvu temp cache (see above). |
| `lib/org/freemedsoftware/module/Reporting.class.php:533` | MAYBE | PART | `readfile($output_file)` with `$output_file = PHYSICAL_LOCATION.'/data/cache/'.$reportprefix.'.'.$ext` and `$reportprefix = $param['report_formatting'].'.'.time()` — a **caller-supplied** value. `GenerateReport_Jasper()` is `protected` and has no caller in this tree (`grep -rn GenerateReport_Jasper` → its own definition only), so it is not reachable here. If a future caller wires it to the relay, the only confinement on the read is the appended `.<ext>` and the requirement that the file exist. Logged as an observation, not a finding. |
| `…/UnfiledDocuments.class.php:90` | PART | YES | `file_get_contents($_FILES['file']['tmp_name'])` — the *name* is PHP's own upload temp path (client cannot choose it); the contents are attacker-supplied, which is the feature. |
| `…/UnfiledDocuments.class.php:184`, `:192` | PART | YES | `file_get_contents($filename)` with `$filename = $this->GetLocalCachedFile($id)` = `data/cache/<table>-md5($id)` (`:512-522`). |
| `…/UnfiledDocuments.class.php:303` | PART | YES | `file_get_contents($new_filename)` — the `move_uploaded_file()`/rename target. |
| `…/UnfiledDocuments.class.php:341` | PART | YES | Djvu page render temp. |
| `…/UnfiledDocuments.class.php:537` | NO | YES | `file_get_contents($hash)` = `data/cache/<table>-md5($id)` (`:530`). |
| `…/UnreadDocuments.class.php:141`, `:262`, `:322` | PART | YES | The same three shapes as UnfiledDocuments (`GetPage`, `GetLocalCachedFile($id)`, `md5($id)`). |
| `lib/org/freemedsoftware/module/UpdatesModule.class.php:160` | PART | NO (**measured**, see §3.1) | `file_get_contents('data/cache/rss.feed.'.$feed)` — `$feed` is request-derived at the relay layer and the sanitiser applied to it in `GetFeed()` is a no-op. |
| `scripts/import_pds.php:53`, `:74` |  |  | CLI import script; `$fn` from its own `readdir` walk. Not served. |
| `scripts/tsmarty2c.php:62` |  |  | CLI build script; `$file` from argv. Not served. |
| `services/RemittCallback.php:30` | NO | YES | `readfile(PHYSICAL_LOCATION.'/data/wsdl/RemittCallback.wsdl')` — compile-time constant path. |
| `services/RemittCallback.php:91` | YES | n/a | `file_get_contents('php://input')` — stream wrapper. |
| `tests/relay.php:28` |  |  | Test harness reading its own fixture (`dirname(__FILE__)."/xmlrpc.msg.txt"`). |
| `tests/transport.php:29` |  |  | Test harness reading `$argv[2]`. |

### 2.3 Second pass — includes the brief's pattern cannot match

```
$ grep -rnE '(include|require)(_once)?[[:space:]]*\([^)]*\$' --include=*.php . \
    | grep -vE '/(net/php/(pear|smarty)|gwtphp|log4php|agata7|acl/adodb)/'
lib/module.php:68      include_once(resolve_module($module_name));
lib/module.php:226     include_once(resolve_module($module_name));
lib/loader.php:94      include_once( $path );
lib/loader.php:104     include_once( $path );
lib/loader.php:146     include_once ( $path );
lib/loader.php:166     if ( file_exists ( $path ) ) { include_once ( $path ); }
lib/org/freemedsoftware/api/FormTemplate.class.php:330   include_once(resolve_module($modulename));
lib/org/freemedsoftware/core/FPDF.class.php:1144         include($this->fontpath.$font);
lib/org/freemedsoftware/core/ModuleIndex.class.php:149   @include_once ( $file );
lib/org/freemedsoftware/core/ModuleIndex.class.php:152   include_once ( $file );
tests/security/module_smoke.php:115                      include_once($root . '/lib/freemed.php');
```
(plus `controller.php:54,68`, which the pattern still cannot match because the
argument starts with `dirname(…)` — measured in §2.1.)

| hit | R? | C? | verdict |
|---|---|---|---|
| `lib/module.php:68`, `:226` (`module_function`, `execute_module`) | YES | YES | The include target is `resolve_module($module_name)` = `ModuleIndex::GetModuleProperty(strtolower($name),'module_path')` — a **registry** value (DB `modules.module_path`, e.g. `lib/org/freemedsoftware/module/Vitals.class.php`). The request-influenced token is a *class name*, never concatenated into the path. Confined to registry paths; a writable `modules` row is a different threat (DB write). Same for `FormTemplate.class.php:330`. |
| `lib/loader.php:94`, `:104`, `:146`, `:166` (`InstantiateClass`, `LoadObjectDependency`) | YES | YES | `$path = ResolveObjectPath($dependency)` — the dotted class path from `CreateObject()`/`LoadObjectDependency()` calls, all code-authored in this tree. |
| `lib/org/freemedsoftware/core/ModuleIndex.class.php:149`, `:152` (`ScanFile`) | NO | YES | `$file` is walked from the module directories by the indexer, not from a request. |
| `tests/security/module_smoke.php:115` | — | — | Test harness. |

### 2.4 Sinks inspected and found confined (one line each, as the brief asks)

* `chtml.php:34/:40` — **was the one unvalidated sibling; validated by this task** (§1).
* `controller.php:48/:54/:68` — `$piece` unvalidated, but the file sink is
  unreachable on PHP 8.3 (fatal at :48, measured) and would any case be confined by
  the forced `controller.` prefix + `.php` suffix + `file_exists()`.
* `relay.php:42/:51` — provider alpha-validated then `file_exists`-gated on
  `Relay_<alpha>.class.php`; method value never becomes a path.
* `help.php:38/:47` — resolved and confined by `lib/help-path.php` (Phase 1).
* `lib/loader.php`, `lib/module.php`, `ModuleIndex.class.php` — include targets are
  registry values or code-authored class paths, never request text.
* `PatientDataStore` (`ResolveFilename`, `GetLocalCachedFile`, `StoreFile`) —
  integer ids/`md5()`, server temp paths.
* `Djvu`, `PDF`, `DicomModule`, `PhotographicIdentification`,
  `UnfiledDocuments`/`UnreadDocuments` — image/render caches named from md5/ids or
  `tempnam()`, plus one literal asset path.
* `LanguageRegistry:115` — directory names taken from a `locale/` listing.
* `ExternalPlugin:162`, `EMRModule:1058`, `ModuleInterface:278/:290`,
  `Reporting:533` — cache/render outputs; the last one is unreachable (protected,
  no caller).
* `services/RemittCallback.php:30` — constant path.
* `lib/org/freemedsoftware/**/adodb/**`, `lib/net/php/smarty/**`,
  `lib/agata7/**`, `FPDF.class.php`, `data/fpdf-fonts/**`, `scripts/**`,
  `tests/**` — vendored libraries, CLI scripts and test harnesses; no request
  value reaches the path on the lines listed.

### 2.5 Known limitation of this audit

The greps are same-line and keyword-based. A path built on one line and used on
another is invisible to them (e.g. `$file` is built at `BaseModule.class.php:166`
and consumed at `:209`). The rows above were extended by hand where the code made
that pattern apparent, but the audit does not claim to be exhaustive for
multi-line dataflow.

### 2.6 `relay-gwt.php` — the fifth entrypoint the PATH_INFO allowlist names (row added in the final fix wave)

Not a grep hit (the file itself never names `PATH_INFO`), so it needs its own
row: `doc/freemed.apache.conf`'s and `docker/nginx.conf`'s PATH_INFO anchoring
both name five entrypoints — `help|chtml|relay|relay-gwt|controller`.php — and
this is the one this audit did not previously cover.

| hit | R? | C? | verdict |
|---|---|---|---|
| `relay-gwt.php:37-38` | YES | n/a | Not a filesystem sink: it starts the GWT servlet (`CreateObject('org.freemedsoftware.core.AuthenticatedRemoteServiceServlet')` then `$servlet->start()`). **What guards it:** `AuthenticatedRemoteServiceServlet::onAfterRequestDecoded` (`:41-47`) runs `checkAuthenticationPolicy` (`:49`) on the decoded method name, which refuses the whole `org.freemedsoftware.core.` namespace (`:51-53`), always allows the two public namespaces (`:56-61`), and for every other method requires an authenticated session — `CallMethod('org.freemedsoftware.public.Login.LoggedIn') == false` → refuse (`:64-67`), with `syslog` on the refusal. So the non-public surface is **SESSION-guarded**, not anonymous. (The harness's `/relay-gwt.php/probe` row answers 200 with a 186–195-byte body — `deployment-hardening.txt:41`, `:62`, `:128`; that body was **not** inspected for this audit and no claim is made about it here.) It composes no filesystem path from request text. **What it does NOT carry:** this transport never consults `Relay_Allowlist` and never calls `Relay::handle_request` (verified: no `Relay_Allowlist` reference anywhere in `lib/gwtphp/**` or in this entrypoint's chain), so the relay allowlist's control does not bound it — recorded in `doc/SECURITY_FOLLOWUP` §4/§6.5, and NOT proposed for gating here (a code gate on this transport is a design change, not a fix). |

---

## 3. Additional sinks found while inspecting (not in the brief's greps)

Recorded because they are the same *family* — a request value reaching a path with
no real validation — and because two of them rest on a sanitiser that does nothing.

### 3.1 `freemed::secure_filename()` is a no-op — and two path sinks trust it

`lib/API.php:938-957` builds its search strings as `"\\$".$secure_var`, which is
the **three-character** sequence `\$.` / `\$/` / `\$\` / `\$|`, not `.` / `/`:

```
$ docker run --rm php:8.3-cli php -r '...same loop...'
search-string for '.'  = '\\$.' (len 3)
'../../etc/passwd' -> '../../etc/passwd'      <- unchanged
'a/b'              -> 'a/b'                   <- unchanged
'a.b'              -> 'a.b'                   <- unchanged
```

Call sites that matter:

1. `UpdatesModule.class.php:124` → `:126 file("data/cache/rss.feed.{$myfeed}")`
   and `:163 fopen('data/cache/rss.feed.'.$feed,'w')`.
   **Measured**, authenticated (admin) relay call to
   `org.freemedsoftware.module.UpdatesModule.GetFeed` (the `modules` registry row
   exists, 135 rows in `modules`):

   * benign `param0=Security` → `Fatal error: Uncaught TypeError: join(): Argument
     #2 ($array) must be of type ?array, false given … UpdatesModule.class.php:126`
     (the cache file does not exist, so `file()` returned `false`);
   * `param0=/../../../data/cache/zzf.xml` with the prefix component created — the
     observable **changes**: the response is no longer the `join()` TypeError but
     the code reaching `MagpieRSS::parse()` (`Failed to create an instance of PHP's
     XML parser`, MagpieRSS.class.php:165 — an unrelated PHP 8 defect:
     `is_resource(xml_parser_create())` is false for PHP 8's `XMLParser` object),
     which only happens when `file()` returned an array, i.e. **the file was read
     three levels above the forced prefix**;
   * the file *content* was **not** reflected — the parse layer fatals first — so
     **no data disclosure was demonstrated**, only the read.

   Confinement in a stock deployment is accidental, not validated: the forced
   prefix is `data/cache/rss.feed.`, so the walk must traverse the component
   `rss.feed.` as a **directory**, which never exists (`file()` → `false` →
   `ENOTDIR`). The read only resolved after `mkdir app/data/cache/rss.feed.` in the
   served copy (removed afterwards). Also measured: from the served tree the walk
   ascends at most to `/var/www` (4 ups) and **not** to `/` — because
   `/var/www/html` is a separate mount in this harness
   (`/dev/nvme0n1p2 on /var/www/html type ext4`), an artefact of the throwaway
   stack, not a property of the application.
   **Verdict: reachable-from-input? YES (authenticated relay method, measured
   dispatch). confined? NO validation; read demonstrated, disclosure not.**
2. `BaseModule.class.php:165-166` — `$_REQUEST['print_template']` into
   `file_exists('lib/tex/'.secure_filename(...).'.tex')`, then the same value into
   `$TeX->RenderFromTemplate($this_template, $rec)`
   (`TeX.class.php:100` → `Smarty` rendering `$template.tex` from
   `data/tex/`, and `smarty_internal_resource_file.php:65` reads it).
   **Not measured** (needs the module print flow and a planted `.tex`): the prefix
   `lib/tex/` ends in a separator, so `../` in `print_template` *can* walk
   directories that exist, but the target must still end in `.tex` and must exist
   under `lib/tex/`, while the file ultimately rendered is its `data/tex/` twin.
   **Verdict: reachable-from-input? YES. confined? NO validation — impact not
   established, recommended follow-up.**

### 3.2 Other observations (no change made, one line each)

* `<layout>`/`<piece>`: the brief's Step-2 grep cannot match `include_once(dirname(`
  at all — see §2.3.
* `Reporting::GenerateReport_Jasper` is `protected` with no caller; if it is ever
  wired to a request, `report_formatting` reaches `readfile()` unvalidated.
* `controller.php` fatals at `:48` for every request on PHP 8.3
  (`Undefined constant "layout"`), which is a functional defect independent of this
  audit — reported, not fixed here.
* Three `${var}` string interpolations in `chtml.php` are deprecated on 8.2+ and
  are pre-existing (`php -l` prints them for `HEAD:chtml.php` too).

---

## 4. What this audit did NOT prove

* No live file read was demonstrated through any PATH_INFO sink on either server.
  Every unfiltered traversal row was refused upstream (400/404) or answered as a
  lookup miss; the one read that *was* demonstrated (§3.1) is a different sink,
  needed an authenticated session plus a directory that does not exist in a stock
  deployment, and did not disclose the content.
* `db`-registry paths (`modules.module_path`) were assumed trustworthy; a threat
  model in which an attacker can write that table was not examined.
* Multi-line path builders outside the greps' reach are not exhaustively covered
  (§2.5).
* Vendored code (Smarty, ADOdb, agata7, FPDF) was judged by call-site inspection,
  not by exercising every entry point of those libraries.
* No check was run against a deployment whose web server hands PHP different
  `PATH_INFO` than the two measured here.
