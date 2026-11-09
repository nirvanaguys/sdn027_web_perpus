# Glossary

Domain terms this codebase uses. Architecture reviews should reuse these names.

- **Catalog (Katalog)** — the collection of ebooks and the one module that knows how
  `/books` is shaped (page of books, summary counts, category list). Both the public
  catalog view and the admin table read it; each view still owns its own filters and
  page size. Lives in `library-frontend/src/composables/useCatalog.js`.
- **Catalog summary (Ringkasan katalog)** — catalog-wide totals (`pdf`, `epub`, `ready`)
  computed by the server over the whole filtered result, ignoring the format filter so
  the PDF/EPUB chips stay meaningful. Never derived from the visible page.
