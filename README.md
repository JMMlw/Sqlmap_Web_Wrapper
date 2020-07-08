# Sqlmap Web Wrapper

SWW is a php web application made to allow multiple users to control multiple sqlmapapi.py orchestrators.

Each user has it's own private endpoint IP. You can eather use mutliples ports from the same IP, even the same IP as the SWW app itself.

Once a user is logged in, he will be able to create multiple tasks, providing arguments or not.

The task will appear in a list and will, once clicked redirect to a detailed view where user can perform multiple actions like retrive attack's data, delete task, dump tables, explore dbs etc.

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
