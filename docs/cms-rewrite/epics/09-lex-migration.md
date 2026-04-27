# Epic 9 — Lex Content Migration

**Sprint:** 6
**Goal:** Lex tags in editorial body content converted to Twill blocks.

Audit and target-block matrix in `specs/lex-tag-audit.md`.

## Checklist

- [ ] Audit SQL: extract every distinct `{{ ... }}` pattern from legacy content fields
- [ ] Document each tag → target block in `specs/lex-tag-audit.md`
- [ ] Build `LexParser` service
- [ ] Implement static-resolve transforms (links, settings, variables)
- [ ] Implement block-replace transforms (form, snippet, gallery)
- [ ] Build legacy-HTML → Twill blocks JSON converter
- [ ] Test on sample of pages, blog posts, page chunks
- [ ] Document unhandled patterns + manual cleanup list
