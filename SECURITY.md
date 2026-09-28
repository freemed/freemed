# Security Policy

FreeMED is an electronic medical record system that holds protected health
information. If you have found a security defect, please report it privately and
give the maintainers a chance to fix it before it is described in public.

<!-- OWNER ACTION REQUIRED before this file is published.  Every <...> below is a
     placeholder: security@<TODO-OWNER-DOMAIN>, <PGP-FINGERPRINT>, <ACK-TARGET>,
     <SUPPORTED-LINE>, <RELEASE-BRANCH>.  Appendix C of doc/SECURITY_ADVISORY is
     the publication checklist; Appendix D lists every placeholder. -->

## Reporting a vulnerability

Send reports to **security@<TODO-OWNER-DOMAIN>** — TODO: the owner must supply
this address.

Encrypt if you can: PGP fingerprint `<PGP-FINGERPRINT>` (TODO). If you cannot
encrypt, say so in the first line and send anyway; an unencrypted report is
better than no report.

Please do **not** file a public issue, PR or forum post about a vulnerability.
Mail the address above.

A useful report tells us:

* the version and how you obtained it (Debian/RPM package, the container, or a
  source checkout), and the deployment layout (Apache + mod_php, or nginx +
  php-fpm);
* a minimal reproduction, or the exact request that shows the defect;
* what the defect needs: a session, a particular role, a non-default option;
* whether you believe PHI is exposed — that is our highest priority;
* how you want to be credited, or that you want to stay anonymous.

## What to expect

* We acknowledge your report within `<ACK-TARGET>` (TODO: pick a target, e.g.
  three business days).
* We tell you whether we can reproduce it, on which release and layout, and what
  we assess the impact to be — including when we cannot reproduce one of your
  steps, and why.
* We tell you the release that carries the fix and, where a CVE is warranted, the
  CVE ID(s) we have requested.
* We credit the reporter in the advisory unless asked not to.
* We may ask you to hold publication until the release is out. We will not ask
  you to hold it indefinitely.

## Supported versions

Only the **most recent release** is supported (TODO: name the supported line and
the branch it is released from, e.g. `<SUPPORTED-LINE>` on `<RELEASE-BRANCH>`).
Security fixes are not backported to older releases or to unreleased snapshots of
older lines.

The defects fixed by the current release are described in
`doc/SECURITY_ADVISORY`. Defects that are known and deliberately **out of scope**
for it — the credential-storage migration, the vendored phpGACL/adodb code, the
legacy `lib/agata7/` surface, and the pre-existing PHP 8.3 defects the fix pass
surfaced — are listed in `doc/SECURITY_FOLLOWUP`. Reports about those items are
welcome, but they are improvements to a known backlog rather than new findings,
and they will be ranked, not treated as emergencies.

This repository has no `CHANGELOG` or release-notes file. The per-change release
note text ships in the operator documents themselves (`doc/RELAY_ALLOWLIST` §4,
`doc/VCALENDAR_AUTHENTICATION` §5).
