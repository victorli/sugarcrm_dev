SaaS Platform base on SugarCRM CE 6.5.23
===
We think Tenant is one group or company, which has their only records,and TenantUser an other type of administrator!<br>
All tenants can only access data created by her/himself except for admin.<br>
TenantUser can have their own child users and employees.<br>
RegularUser do not allow to create user and employee, and no rights to access other user or employee information.<br>

Changes:
---
* added new user type "TenantUser"
* added new field "is_tenant" in User module, and when admin creating TenantUser, this field set True
* added new field "tenant_id" in each module, and when creating record,siggend the creator's tenant to this field
* update each vardefs.php to support tenant_id field
* override create_new_list_query() function,in Basic class, to filter records
* modified view.detail.php to make each user can only view records with the same tenant with it
* added TenantPeriods module to manage the using periods of each module for current tenant
* added SugarField Photo to process upload photo
* added save_photo function in SugarBean to process photo save processing

Fixed Bugs:
---
* bug 56131: In SugarView.php, function getModuleTitle() does not really using the override function getHelpText() of each child class of SugarView
