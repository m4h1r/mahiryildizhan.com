// Admin entry point. Admin-only helpers live here so the public site never
// ships them. app.js starts Alpine in a microtask, after this whole module
// graph (including the helpers below) has evaluated.
import './app';
import './admin/shell';
import './admin/crm';
