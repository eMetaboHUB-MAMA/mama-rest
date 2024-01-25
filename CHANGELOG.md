# Changelog

Technical change between REST API releases

<!--
## Template
- **@tag**: `xxx`
- **@version**: `xxx`
- **@notes**:
  - describe this release's reasons
- **@new**:
  - mama#xx - short description - short_sha or merge_request
- **@bugs fixed**:
  - mama#xx - short description - short_sha or merge_request
- **@other**:
  - mama#xx - short description - short_sha or merge_request
- **@known bugs**:
  - mama#xx - short description
-->

## Latest

- **@version**: `v1.0.5`
- **@new**:
  - mama#32 - add "confirmation box/message" when a request is rejected
  - mama#33 - get a list of "rejected" requests with associated "rejected reasons" in `statistics view` and `projects XLS file export`
  - mama#35 - get 'response delay' (in days) indicator in `projects XLS file export` and `GET projects web GUI` (only for PM)
  - mama#36 - update `search/list projects` view and service:
    - add "owner email pattern filter"
    - "in charge email pattern filter"
    - "involved email pattern filter"
  - mama#42 - update `search/list projects` view: improve "plateforms" filter (label + select multiple)
  - mama#43 - update `search/list projects` XLS export:
    - add `administratif context` column 
    - add `geographical context` column
    - add `public/private context` column
  - mama#46 - statistics view: add new `funding source/types` graph.; also improve graph. listing
  - mama#47 - get project owner information contact (add `phone` and `address` in `project` view / preview)
  - mama#48 - update `search/list projects` service: improve "plateforms" filter (support select multiple from mama#42)
- **@bugs fixed**:
  - mama#34 - reject / paused "reason text" is now optional
  - mama#37 - increase 'messages' and 'appointments' modals sizes; increase 'MTH dialog box' size (projects' extra-data)
- **@other**:
  - mama#26 - update CI file to improve releases
  - mama#27 - update CI config. and Docker images for tests
  - mama#28 - add users' email addresses in XLS file export
  - mama#51 - update composer dependencies
  - mama#52 - update matomo analytics code

<!-- 
- **@notes**:
  - describe this release's reasons
- **@new**:
  - mama#xx - short description - short_sha or merge_request
- **@bugs fixed**:
  - mama#xx - short description - short_sha or merge_request
- **@other**:
  - mama#xx - short description - short_sha or merge_request
- **@known bugs**:
  - mama#xx - short description
-->

## Previous releases

### 2020-10-14

- **@version**: `v1.0.4`
- **@bugs fixed**:
  - mama#22 - fix HTTP/HTML links in emails
  - mama#23 - fix "clean restricted informations on NULL object" error
- **@other**:
  - mama#20 - update CI to match prod. env. - mama@f7ce718d
  - mama#21 - display webapp and rest api versions in footer
  - mama#12 - switch an analysis request back to `waiting` status
  - mama#24 - manage reject a project because `saved twice` reason

### 2020-05-14

- **@version**: `v1.0.3`
- **@notes**:
  - bugfix
- **@new**:
  - mama-webapp#52 - add CI/CD pipeline - mama-rest@7f5d8cee
  - mama-webapp#55 - add confirmation dialog when a project manager is set
  - mama-webapp#56 - allow users and manager to remove uploaded file (scientific context)
  - mama#2 - add `dialog box` support (mama#8, mama#9)
- **@bugs fixed**:
  - mama-webapp#51 - update PHPExcel library - mama-rest@cd709e80
  - mama#18 - increase text size for scientific context and add GUI control
- **@other**:
  - mama-webapp#57 - update Matomo tracker code - mama-webapp@d02eab4e
  - mama#14 - set "inactive user" timeout from 6 months to 2 years
  - mama#15 - reject projects form field are now mandatory

Third part dependencies for MAMA-REST
```sh
# for PHP-Excel
sudo apt-get install -y php7.0-gd php7.0-mbstring php7.0-zip
```

Update SQL query:
```sql
mysql> UPDATE   users
    -> SET      email = CONCAT(LEFT(email, INSTR(email, '@')), 'inrae.fr')
    -> WHERE    email LIKE '%@inra.fr%' AND login NOT LIKE '%@%';
```

### 2017-12-15

- **@version**: `v1.0.2`
- **@notes**:
  - minor release
  - fix orthography errors (webapp)

### 2017-12-15

- **@version**: `v1.0.1`
- **@notes**:
  - minor release
  - fix orthography errors (webapp)

### 2017-07-06

- **@version**: `v1.0.0`
- **@notes**:
  - first MAMA SI release
