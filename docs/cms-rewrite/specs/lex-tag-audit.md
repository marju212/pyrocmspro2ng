# Lex tag audit

Every distinct Lex tag pattern found in editorial body content (pages, blog posts, page chunks, snippets). Drives Epic 9.

## Audit SQL (run against `bockavel_legacy`)

```sql
SELECT body FROM bockavel_pages WHERE body LIKE '%{{%';
SELECT body FROM bockavel_blog WHERE body LIKE '%{{%';
SELECT body FROM bockavel_page_chunks WHERE body LIKE '%{{%';
SELECT body FROM bockavel_snippets WHERE body LIKE '%{{%';
```

Extract unique tag patterns with regex `\{\{\s*[a-z_]+:[a-z_]+(\s+[^}]*)?\}\}`.

## Tag → block mapping

| Lex tag pattern | Migration target | Status |
|---|---|---|
| `{{ pages:link id="X" }}` | Static-resolve to `<a href="/slug">title</a>` in a text block | TBD |
| `{{ settings:* }}` / `{{ variables:* }}` | Static-resolve to literal value in text block | TBD |
| `{{ pyroforms:form id="X" }}` | Convert to typed `form_block` referencing migrated form | TBD |
| `{{ snippets:foo }}` | Inline snippet content, OR convert to typed `snippet_block` | TBD |
| `{{ blog:recent }}` and similar dynamic listings | Convert to typed `recent_posts_block` rendered via Livewire | TBD |
| Plain HTML between tags | Wraps into `text_block` | TBD |
| [add patterns from audit] | | |

## Unhandled

Patterns that need editorial cleanup rather than automated migration. List during audit.

## Theme template tags (NOT in scope)

`{{ navigation:links }}`, `{{ pages:children }}`, etc. — these live in theme templates, not editorial content. Replaced by Blade includes / Livewire components during Epic 7. No data migration concern.
