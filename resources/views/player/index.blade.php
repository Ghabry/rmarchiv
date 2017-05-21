<!doctype html>
<html lang="en-us">
<head>
    <meta charset="utf-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>{{ trans('player.title') }}</title>
    <style>
        body {
            font-family: sans-serif;
            margin: 0;
            padding: none;
            color: white;
            background: black;
        }

        canvas.emscripten {
            border: 0px none;
            width: 320px;
            height: 240px;
            max-width: 100%;
            max-height: 100%;
            position: absolute;
            top: 50%;
            left: 50%;
            -webkit-transform: translate(-50%, -50%);
            -ms-transform: translate(-50%, -50%);
            transform: translate(-50%, -50%);
        }

        #status {
            font-weight: bold;
            color: #888888;
            text-align: center;
            margin: auto;
        }

        #controls {
            text-align: right;
        }

        #controls input {
            border: 1px solid gray;
            background: black;
            color: gray;
        }

        @media all and (min-width: 640px) {
            canvas.emscripten {
                width: 640px;
                height: 480px;
                image-rendering: optimizeSpeed; /* Older versions of FF          */
                image-rendering: -moz-crisp-edges; /* FF 6.0+                       */
                image-rendering: -webkit-optimize-contrast; /* Safari                        */
                image-rendering: -o-crisp-edges; /* OS X & Windows Opera (12.02+) */
                image-rendering: pixelated; /* Awesome future-browsers       */
                -ms-interpolation-mode: nearest-neighbor; /* IE                            */
            }
        }

        @media all and (max-width: 639px) {
            canvas.emscripten {
                width: 320px;
                height: 240px;
            }
        }
    </style>
</head>
<body>

<div id="controls">
    <input type="button" value="Full screen" onclick="if (Module.requestFullScreen) Module.requestFullScreen()">
</div>

<div id="status">{{ trans('player.index.downloading') }}</div>

<canvas class="emscripten" id="canvas" oncontextmenu="event.preventDefault()"></canvas>

<script type='text/javascript'>
    var statusElement = document.getElementById('status');

    var Module = {
        preRun: [],
        postRun: [],
        print: (function () {
            var element = document.getElementById('output');
            if (element) element.value = ''; // clear browser cache
            return function (text) {
                if (arguments.length > 1) text = Array.prototype.slice.call(arguments).join(' ');
                console.log(text);
                if (element) {
                    element.value += text + "\n";
                    element.scrollTop = element.scrollHeight; // focus on bottom
                }
            };
        })(),
        printErr: function (text) {
            if (arguments.length > 1) text = Array.prototype.slice.call(arguments).join(' ');
            console.error(text);
        },
        canvas: (function () {
            var canvas = document.getElementById('canvas');

            // As a default initial behavior, pop up an alert when webgl context is lost. To make your
            // application robust, you may want to override this behavior before shipping!
            // See http://www.khronos.org/registry/webgl/specs/latest/1.0/#5.15.2
            canvas.addEventListener("webglcontextlost", function (e) {
                alert('{{ trans('player.index.webgl_error') }}');
                e.preventDefault();
            }, false);

            return canvas;
        })(),
        setStatus: function (text) {
            if (!Module.setStatus.last) Module.setStatus.last = {time: Date.now(), text: ''};
            if (text === Module.setStatus.text) return;
            statusElement.innerHTML = text;
        },
        totalDependencies: 0,
        monitorRunDependencies: function (left) {
            this.totalDependencies = Math.max(this.totalDependencies, left);
            Module.setStatus(left ? '{{ trans('player.index.prep') }} (' + (this.totalDependencies - left) + '/' + this.totalDependencies + ')' : '{{ trans('player.index.gamedata_download') }}');
        }
    };
    Module.setStatus('{{ trans('player.index.downloading') }}');
    window.onerror = function (event) {
        Module.setStatus('{{ trans('player.index.exception') }}');
        Module.setStatus = function (text) {
            if (text) Module.printErr('[post-exception status] ' + text);
        };
    };

    var RMFS = {
        mount: function(mount) {
            // reuse all of the core MEMFS functionality
            return MEMFS.mount.apply(null, arguments);
        },
        to_binary: function(dataURI){
            var base64 = dataURI.substring(0);
            var raw = window.atob(base64);
            var rawLength = raw.length;
            var array = new Uint8Array(new ArrayBuffer(rawLength));

            for(i = 0; i < rawLength; i++) {
                array[i] = raw.charCodeAt(i);
            }

            return array;
        },
        syncfs: function(mount, populate, callback) {
            //if (err) return callback(err); TODO Error handling
            if (populate) {
                var xhr = new XMLHttpRequest();
                xhr.onreadystatechange = function () {
                    if (xhr.readyState === 4) {
                        if (xhr.status === 200) {
                            var obj = JSON.parse(xhr.responseText);

                            for (var property in obj) {
                                if (obj.hasOwnProperty(property)) {
                                    var nal = property.length == 1 ? "0" : "";
                                    var bin = RMFS.to_binary(obj[property]);
                                    var stream = FS.open(mount.mountpoint + "/Save" + nal + property + ".lsd", "w");
                                    FS.write(stream, bin, 0, bin.length, 0);
                                    FS.close(stream);
                                }
                            }
                        } else {
                            console.log('Error: ' + xhr.status);
                        }
                    }
                };
                xhr.open('GET', "{{ url('/') }}" + '/savegames/{{ $gamefileid }}');
                xhr.send(null);
            } else {
                var xhr = new XMLHttpRequest();
                xhr.onreadystatechange = function () {
                    if (xhr.readyState === 4) {
                        if (xhr.status !== 200) {
                            console.log('Error: ' + xhr.status);
                        }
                    }
                };
                xhr.open('POST', "{{ url('/') }}" + '/savegames/{{ $gamefileid }}');

                var obj = {}
                FS.readdir(mount.mountpoint).forEach(function(x) {
                    var num = parseInt(x.substr(4,2));
                    if (!isNaN(num) && num >= 1 && num <= 15) {
                        obj[num.toString()] = btoa(String.fromCharCode.apply(null, FS.readFile(mount.mountpoint + "/" + x)));
                    }
                });
                xhr.send(JSON.stringify(obj));
            }

            callback(null);
        }
    };
    Module.EASYRPG_FS=RMFS;
</script>
<script>

    (function () {
        var memoryInitializer = "{{ url('/') }}" + '/index.html.mem';
        if (typeof Module['locateFile'] === 'function') {
            memoryInitializer = Module['locateFile'](memoryInitializer);
        } else if (Module['memoryInitializerPrefixURL']) {
            memoryInitializer = Module['memoryInitializerPrefixURL'] + memoryInitializer;
        }
        var xhr = Module['memoryInitializerRequest'] = new XMLHttpRequest();
        xhr.open('GET', memoryInitializer, true);
        xhr.responseType = 'arraybuffer';
        xhr.send(null);
    })();

    var script = document.createElement('script');
    script.src = "{{ url('/') }}" + "/index.js";
    document.body.appendChild(script);

</script>
</body>
</html>
