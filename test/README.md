# Test Scripts

These scripts are CLI-only and use the same DB config as `includes/db.php`.

## Members

Add a member:

```bash
php test/add_member.php --name "Jane Doe" --email "jane@example.com" --password "secret" --phone "123456" --address "Port Harcourt" --next-of-kin "John Doe" --status approved
```

Delete a member:

```bash
php test/delete_member.php --email "jane@example.com"
```

or

```bash
php test/delete_member.php --id 123
```

## Admins

Add an admin:

```bash
php test/add_admin.php --username "admin2" --password "secret"
```

Delete an admin:

```bash
php test/delete_admin.php --username "admin2"
```

## Quick Lists

List members:

```bash
php test/list_members.php
```

List members by status:

```bash
php test/list_members.php --status approved
```

List admins:

```bash
php test/list_admins.php
```
