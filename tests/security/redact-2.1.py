#!/usr/bin/env python3
# redact-2.1.py — redaction filter for the Task 2.1 pre-fix exploit transcript.
#
# Rules (mandatory per the task contract):
#   * no full 32-hex digest: the first 4 characters + U+2026 are kept
#   * no full username: any JSON string on a line that carried a digest is
#     truncated to its first 4 characters + U+2026
#   * no credential of any kind, no PHI
# Row counting is done on the RAW body before redaction.
import re
import sys

DIGEST = re.compile(r'\b([0-9a-f]{4})[0-9a-f]{28}\b')
JSONSTR = re.compile(r'"([^"\\]{2,64})"')
KEYCTX = re.compile(r'\s*:')   # a string followed by ':' is a JSON key, never redacted


def row_count(raw: str) -> int:
    """Count the rows of a JSON array-of-arrays response, cheaply and honestly."""
    s = raw.strip()
    if not s or s == 'null':
        return 0
    m = re.match(r'^\[\[.*\]\]$', s, re.S)
    if not m:
        return -1  # not the row-array shape; caller must not claim a count
    return s.count('],[') + 1


def redact(text: str) -> str:
    """Redact values, never keys: a JSON string followed by ':' is a key.

    A line is value-redacted when it carries a digest; lines that leak other
    columns (e.g. the picklist site leaks userfname/userlname as keys) are
    redacted by hand in the evidence file and flagged there.
    """
    out = []
    for line in text.split('\n'):
        line = DIGEST.sub(lambda m: m.group(1) + '\u2026', line)
        if '\u2026' in line:
            line = JSONSTR.sub(
                lambda m: m.group(0) if KEYCTX.match(line[m.end():]) else '"' + m.group(1)[:4] + '\u2026"',
                line)
        out.append(line)
    return '\n'.join(out)


def main() -> int:
    raw = sys.stdin.read()
    sys.stdout.write(redact(raw))
    sys.stderr.write('\n[rows=%d]\n' % row_count(raw))
    return 0


if __name__ == '__main__':
    raise SystemExit(main())
