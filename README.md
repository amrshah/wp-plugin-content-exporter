# WP Content Exporter

WP Content Exporter is a simple WordPress admin plugin that lets you view, filter, and export posts, pages, and custom post types (including Pods CPTs) in CSV format.

The plugin adds a new menu item under **Tools → Content Exporter**, where you can select which post types to include, browse through paginated results, and export your data.

---

## Features

- Supports **all registered post types**, including custom ones (Pods, ACF CPTs, etc.)
- Displays post **ID, type, title, permalink, and status**
- Each post ID is linked to the WordPress edit screen
- Clean AJAX-based UI with pagination
- Options to **export all posts, current page, or only selected rows**
- CSV export ready for Excel or Google Sheets
- Works with both standard and custom permalink structures
- Automatically handles draft or unpublished post URLs

---

## Installation

1. **Download or clone this repository**  
   ```bash
   git clone https://github.com/amrshah/wp-plugin-content-exporter.git
   
   ```

2. **Upload the plugin**  
   - Option A: Upload the folder `wp-content-exporter` to `/wp-content/plugins/`  
   - Option B: Compress it as a `.zip` and install via **Plugins → Add New → Upload Plugin**

3. **Activate the plugin**  
   Go to **Plugins → Installed Plugins** and click **Activate** under *WP Content Exporter*.

4. **Access the exporter**  
   In your WordPress admin sidebar, go to:
   ```
   Tools → Content Exporter
   ```

---

## Usage

1. Select the post types you want to include (Posts, Pages, or any CPTs).
2. Click **Generate List**.
3. Browse results in a paginated table.
4. Use the following export options:
   - **Export All** – Downloads a CSV of all records for selected post types.
   - **Export Selected** – Only exports rows you’ve checked.
   - **Export Current Page** – Exports the posts currently displayed (default).

---

## CSV Output

The exported CSV file includes the following columns:

| Column | Description |
|---------|--------------|
| ID | WordPress post ID |
| Post Type | The registered post type slug |
| Title | The post or page title |
| URL | The permalink (auto-generated even for drafts) |
| Status | Post status (publish, draft, pending, etc.) |

---

## Requirements

- WordPress 5.8 or higher  
- PHP 7.4 or higher  
- Administrator privileges to access Tools menu and export data

---

## Development Notes

- The plugin uses `admin-ajax.php` for dynamic data loading and export.
- Pagination and exports are handled via AJAX, keeping the UI responsive.
- Drafts and unpublished posts are assigned a generated "pretty permalink" based on their title and CPT rewrite rules.
- No third-party dependencies are required.

---

## Changelog

**Version 0.0.4**
- Added support for exporting only selected rows
- Improved UI with better layout and buttons
- Fixed CSV export for all pages and selected rows
- Enhanced permalink generation for CPTs and drafts

**Version 0.0.3**
- Added "Export All" functionality
- Added pagination for large datasets

**Version 0.0.2**
- Improved admin UI layout
- Added loading indicators and better styling

**Version 0.0.1**
- Initial release with basic table and CSV export

---

## License

This plugin is licensed under the [GPLv2 or later](https://www.gnu.org/licenses/gpl-2.0.html).

---

## Credits

Developed by the **AlamiaSoft** team.

For support, improvements, or contributions, please open an issue or submit a pull request on GitHub.
