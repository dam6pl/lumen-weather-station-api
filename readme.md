# Weather Station - REST API Services

This is a RESTful API services for Weather station. Service has been written in Lumen 5.4 and using MySQL database.

Service is avaliable on https://weather-station-api.herokuapp.com/.

**Author:** [Damian Nowak](mailto:me@dnowak.dev)


## Endpoints

* GET `/v1/stations` - Returns list of all active stations.
* GET `/v1/stations/:id` - Returns single station details.
* GET `/v1/stations/:id/measurements` - Returns measurements for single station.
* POST `/v1/stations` - Create new station.
* POST `/v1/stations/:id` - Edit single station.
* DELETE `/v1/stations/:id` - Delete single station.

* GET `/v1/measurements` - Returns list of all active measurements.
* GET `/v1/measurements/:id` - Returns single measurement details.
* POST `/v1/measurements` - Create new measurement.
* POST `/v1/measurements/:id` - Edit single measurement.
* DELETE `/v1/measurements/:id` - Delete single measurement.

* GET `/v1/users` - Returns list of all active users.
* GET `/v1/users/:id` - Returns single user details.
* GET `/v1/users/:id/token` - Returns single user token.
* POST `/v1/users` - Create new user.
* POST `/v1/users/:id` - Edit single user.
* DELETE `/v1/users/:id` - Delete single user.

### Authentication
Part of endpoints are avaliable only for users with correct permissions. Authentication can be done by parameter (`token`) or by header (`X-Token-Auth`).
