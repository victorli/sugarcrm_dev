SaaS Platform base on SugarCRM CE 6.5.23
===
We think Tenant is one group or company, which has their only records,and TenantUser an other type of administrator!<br>
All users can only access data created by her/himself except for admin.<br>
TenantUser can have their own child users and employees.<br>
RegularUser do not allow to create user and employee, and no rights to access other user or employee information.<br>

Changes:
---
* added new user type "TenantUser"
* added new field "is_tenant" in User module, and when admin creating TenantUser, this field set True
