# Epic 3 — Editorial Blocks

**Sprints:** 2 (Stage 3a) + 3 (Stage 3b) — parallel with Epic 2.
**Goal:** all block types editors will use in body content.

**Sequencing:** blocks split by module dependency.

- **Stage 3a — base blocks (no module deps):** Sprint 2, fully parallel with Stage 2a.
- **Stage 3b — module-dependent blocks:** Sprint 3, after the modules they reference exist (Stage 2a + 2b).

## Stage 3a — base blocks (Sprint 2)

- [ ] `text_block` (rich text)
- [ ] `image_block` (single image + caption — Twill media library)
- [ ] `gallery_block` (multiple images — Twill media)
- [ ] `video_block` (oEmbed)
- [ ] `cta_block` (heading + text + button)
- [ ] `divider_block`

## Stage 3b — module-dependent blocks (Sprint 3)

| Block | Depends on |
|---|---|
| `form_block` | `Form` module (Stage 2a) |
| `snippet_block` | `Snippet` module (Stage 2a) |
| `file_download_block` | `RestrictedFile` module (Stage 2b) |
| `restricted_section_block` | `Group` module (Stage 2a) + member auth (Epic 4) |
| `agenda_block` | `Calendar` module (Stage 2b) |
| `sponsor_strip_block` | `Sponsor` module (Stage 2b) |

- [ ] `form_block` (browser to Form)
- [ ] `snippet_block` (browser to Snippet)
- [ ] `file_download_block` (browser to RestrictedFile)
- [ ] `restricted_section_block` (children + required_groups)
- [ ] `agenda_block` (renders upcoming Calendar entries — global feed or filtered by current page)
- [ ] `sponsor_strip_block` (inline sponsor logos; sidebar placement handled in layout, not as an editorial block)
- [ ] [add others as Lex audit reveals — see `specs/lex-tag-audit.md`]
