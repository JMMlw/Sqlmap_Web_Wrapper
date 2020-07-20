# Sqlmap Web Wrapper

SWW is a php web application made to allow multiple users to control multiple sqlmapapi.py orchestrators.

Each user has it's own private endpoint IP. You can eather use mutliples ports from the same IP, even the same IP as the SWW app itself.

Once a user is logged in, he will be able to create multiple tasks, providing arguments or not.

The task will appear in a list and will, once clicked redirect to a detailed view where user can perform multiple actions like retrive attack's data, delete task, dump tables, explore dbs etc.

## Usage
### Create a new recon task

For beggining, just enter a target url. all GET parameters and all POST forms are gonna be scanned.
Once you created a new task, it appears in the dashboard as bellow
![Alt text](/screenshots//dashboard.png?raw=true "Dashboard")

### Customize your target, create offensive task

Once the recon task has run, if the injection has succeced, you will have the database structure avaible and you will be able to target specifics tables and databases as bellow.
![Alt text](/screenshots//panel_1.png?raw=true "Recon task panel")
When you are good you can lunch a new dump task with the button. Tasks will appear in the dashboard like few screens ago.
![Alt text](/screenshots//dashboard_1.png?raw=true "Dashboard with multiples tasks")

### Get live logs from the sql injection server

You can watch what is going on in the logs view.
![Alt text](/screenshots//panel.png?raw=true "Dump task panel")

### Visualise data

... TODO
... TODO

### Advanced scanning menu

... TODO
... TODO

### Explore privileges

... TODO
... TODO

### Modify databases

... TODO
... TODO

### Read / Write files

... TODO
... TODO

### Elevate privileges

... TODO
... TODO

## Disclaimer
This project has been created because the only existing application that match this need isn't free, open source and is expensive. Btw I'm not a web developper and it took me like 6 hours to make it work.


## Installation

### XAMPP

Drag the files in your htdocs folder, import the .sql file.

### SQL

Insert a new user and specify the custom endpoint with the sqlmap endpoint like follow :
```
https://420.420.420.420:1337
```


## License
[MIT](https://choosealicense.com/licenses/mit/)
