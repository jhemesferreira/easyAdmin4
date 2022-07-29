## Useful tips

1- Install easyAdmin 4: composer require admin  
2- Make a dashboard: symfony console make:admin:dashboard  
3- Using only attribute IsGranted('ROLE_ADMIN') is not enough, <br/> we need to configurate the access_control on security.yaml { path: ^/admin, roles: ROLE_ADMIN }  
4- Create CRUD: symfony console make:admin:crud