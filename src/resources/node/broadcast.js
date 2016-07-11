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
var channels = [];
io.sockets.on('connection', function (socket) {

    socket.on('register-channels', function (data) {
        console.log("Registering user channels...")
        user = data.user;
        var channels = user.channels,
            chans = {};

        // if (!channels.contains(user.channel)&& channels.length > 0) {
        //     channels.concat(user.channel);
        // }
        if(!channels.contains(user.channel))
            channels.push(user.channel);
        else console.log("NOPE!")
        console.log('');
        console.log("User " + JSON.stringify(user));
        console.log('');
        console.log('');
        console.log('Channels: ' + JSON.stringify(channels));
        console.log('');
        // channels[user.id] = [];
        chans[user.id] = []
        for (var i = 0; i < channels.length; i++) {
            console.log("channel length: " + JSON.stringify(channels) + " -- " + i)
            redis.subscribe(channels[i].uuid);
            socket.join(channels[i].uuid);
            console.log("Registered " + user.name + channels[i].uuid)
            // to(socket, channels[i].uuid, {description:'Welcome back '+user.name , type: 'general'});
            chans[user.id].push(channels[i].uuid);
        }
        console.log(JSON.stringify(chans));

    });
    socket.on('get-response', function(data){
        to(socket, user[0].uuid, data);
    });
    socket.on('read-notification', function (data) {
        var notify = (data) ;
        request({
            method: 'PUT',
            url: process.env.APP_URL+'/radio/api/notification/' + notify.notification_id + '/read',
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
                console.log("Read notification");
                // to(socket, notify.uuid, "Read notification");
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
    if(typeof data === "object")
    {
        data = JSON.stringify(data);
    }
    console.log("Emiting --" + data + "-- to " + channel);
    io.sockets.in(channel).emit('notify', data);
}
console.log("Server starting...")
server.listen(process.env.NODE_PORT);
console.log("Server started...")
Array.prototype.contains = Array.prototype.contains || function(obj) {
        var x;
        for (x in obj) {
            if (obj.hasOwnProperty(x) && obj[x] === obj) {
                return true;
            }
        }

        return false;
    };
