<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Forte API</title>

        <!-- Fonts -->
        <link href="https://fonts.googleapis.com/css?family=Nunito:200,600" rel="stylesheet">

        <!-- Styles -->
        <style>
            html, body {
                background-color: #fff;
                color: #636b6f;
                font-family: 'Nunito', sans-serif;
                font-weight: 200;
                height: 100vh;
                margin: 0;
            }

            .full-height {
                height: 100vh;
            }

            .flex-center {
                display: flex;
                justify-content: center;
            }

            .position-ref {
                position: relative;
            }

            .top-right {
                position: absolute;
                right: 10px;
                top: 18px;
            }

            .content {
                text-align: left;
            }

            .links > a {
                color: #636b6f;
                padding: 0 25px;
                font-size: 13px;
                font-weight: 600;
                letter-spacing: .1rem;
                text-decoration: none;
                text-transform: uppercase;
            }

            .m-b-md {
                margin-bottom: 30px;
            }
        </style>
    </head>
    <body>
        <div class="flex-center position-ref full-height">
            <div class="content">

            </div>
        </div>

        <script src="https://cdnjs.cloudflare.com/ajax/libs/showdown/1.8.5/showdown.min.js" integrity="sha256-rnlCzq7mhN7HlGWkWJ539aucrpHWZOFa/9SqlQvKxjQ=" crossorigin="anonymous"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>

        <script>
            let README_URL = 'https://raw.githubusercontent.com/team-crescendo/laravel-forte-api/refactoring/master/README.md';

            $.ajax({
                url: README_URL,
                success: function (data) {
                    let converter = new showdown.Converter(),
                        text      = data,
                        html      = converter.makeHtml(text);

                    $('.content').html(html);
                }
            });

            console.log(
                '전역까지 ' +
                Math.floor((new Date("April 30, 2022").getTime() - new Date()) / (1000 * 60 * 60 * 24)) + '일 남았습니다.'
            );
        </script>
    </body>
</html>
