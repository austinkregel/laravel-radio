console.log("Starting server...");
var dotenv = require('dotenv').config();
var http = require('http'),
    server = http.Server();
var io = require('socket.io')(server);
var Redis = require('ioredis');
var request = require('request');
console.log("setting up redis...");
var redis = new Redis(),
    user = null;
var has_joined = false;

io.sockets.on('connection', function (socket) {
    socket.on('register-channels', function (data) {
        console.log("Registering user channels...")
        console.log(user = data);
        for (var i = 0; i < data.length; i++) {
            redis.subscribe(data[i].uuid);
            socket.join(data[i].uuid);
            console.log("Registered to: " + data[i].uuid)
            to(socket, data[i].uuid, 'Welcome to the channel!');
        }
    });
    socket.on('get-response', function(data){
        to(socket, user[0].uuid, data);
    });
    socket.on('read-notification', function (data) {
        var notify = (data) ;
        request({
            method: 'PUT',
            url: process.env.APP_URL+'/api/notification/' + notify.notification_id + '/read',
            auth: {
                'bearer': notify.token
            },
            multipart: [{
                'content-type': 'application/json',
                body: JSON.stringify({
                    '_token': notify._token,
                    'notification_id': notify.notification_id,
                    'user_id': notify.user_id
                })
            }]
        }, function (err, httpResponse, body) {
            if(is_json(body)){
                var error = JSON.parse(body);
                if(error.error !== undefined){
                    console.log("Failed to read notification. " + error.error)
                    return;
                }
                to(socket, notify.uuid, "Read notification");
            }else {
                console.log(err, httpResponse); // Show the HTML for the Google homepage.
                var fs = require('fs');
                fs.writeFile("public/error.html", data.token + body, function (err) {
                    if (err) {
                        return console.log(err);
                    }
                    console.log("The file was saved!");
                });
            }
        });

    });
    function  is_json(body){
        if(typeof body != 'string'){
            body = JSON.stringify(body);
        }
        try{
            JSON.parse(body);
            return true;
        } catch (e) {
            return false;
        }
    }
    /*
     * We need to be able to send the emit a socket event when we get a redis event.
     * but we also can't keep doing redis.on because the app will creash...
     * Use this hack instead, because this will stay constant for as long as the app runs.
     */
    if (has_joined === false) {
        console.log("Registering Redis.on(message, closure)...")
        redis.on('message', function (event_channel, data) {
            var json_data =  JSON.parse(data);

            console.log("MESSAGE",event_channel, JSON.stringify(json_data));
            to(socket, event_channel, JSON.stringify(json_data));
        });
        has_joined = true;
        console.log("Registered Redis.on(message, closure)...")
    }
});

function to(socket, channel, data) {
    console.log("Emiting --" + data + "-- to " + channel);
    io.sockets.in(channel).emit('notify', data);
}
console.log("Server starting...")
server.listen(42890);
console.log("Server started...")
