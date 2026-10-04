Prrepl v40 — office-scoped employees

When a Super Admin opens:
/admin/employees.php?office_id=2
only employees belonging to office 2 are listed.
The office context is preserved for Create and Edit links.
Without office_id, Super Admin sees all employees.
Regular office users remain scoped to their own office.
