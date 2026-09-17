# Product

<!-- impeccable:product-schema 1 -->

## Platform

web

## Users
Individual users needing personal and private file transfers, file archiving, and quick text note drops across their personal devices (PC, laptop, smartphone) over a local network (LAN) or self-hosted cloud environment.

## Product Purpose
Provide an instant, lightweight self-hosted hub to upload, organize, download, and delete files with clear storage quota awareness, as well as quickly store text snippets without complex setup or overhead.

## Positioning
A zero-barrier, self-contained personal file and text station with direct drag-and-drop mechanics, immediate visual feedback on storage capacity, and zero external service lock-in.

## Operating Context
Accessed via modern web browsers on desktop and mobile devices within a home or personal private network. Users frequently drop files or clipboard text to transfer between phone and computer or keep temporary working files accessible.

## Capabilities and Constraints
- **Upload Limits:** Per-file limit of 15MB, aggregate storage quota capped at 5GB (`uploads/` directory).
- **File Management:** List files with metadata (size, last modified date), sort by name/date/size with ascending/descending toggle.
- **Batch Operations:** Multi-select with "select all", batch download (bundled into a ZIP via client-side JSZip or direct stream), and batch delete.
- **Text Drops:** Quick text input for sending and recording text notes directly into the manager.
- **Theming:** Dual light/dark mode support with persistent state.
- **Technical Architecture:**
  - Backend must remain PHP endpoints (`upload.php`, `files.php`, `delete.php`, `download.php`, `list.php`).
  - Frontend is currently Vanilla HTML5/CSS3/JavaScript, with confirmed openness to refactoring into a modern frontend framework when needed.
  - Interface copy is currently localized in Vietnamese (Tiếng Việt).

## Brand Commitments
- Name: "BK Space"
- Icon asset: `folder.png`
- Clean, focused utility feel with minimal clutter and clear status signals.

## Evidence on Hand
- Functional single-page interface: `index.html` and stylesheet `css/style.css`.
- Functional backend API scripts: `files.php`, `upload.php`, `delete.php`, `download.php`, `list.php`.
- Local storage directory: `uploads/`.

## Product Principles
1. **Instant Utility:** Every core action (drop file, drop text, download) should require minimal steps with zero latency friction.
2. **Transparent Resource Limits:** Quota usage, storage capacity, and individual file sizes should be clearly visible at a glance.
3. **Safe & Decisive Operations:** Bulk operations (downloading archives, deleting files) should provide clear status feedback and prevent accidental loss.
4. **Fluid Responsiveness:** Must remain effortless to navigate and interact with whether on a widescreen desktop monitor or a mobile viewport.
