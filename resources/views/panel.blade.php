<!doctype html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">

    <title>FORTE STORE</title>

    <meta property="og:title" content="팀 크레센도 포르테 청약철회">
    <meta property="og:description" content="팀 크레센도 청약철회">
    <meta property="og:site_name" content="팀 크레센도 청약철회">
    <meta property="og:image" content="https://avatars2.githubusercontent.com/u/41981145?s=200&v=4">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="apple-touch-icon" sizes="57x57" href="{{ asset('img/favicon/apple-icon-57x57.png') }}">
    <link rel="apple-touch-icon" sizes="60x60" href="{{ asset('img/favicon/apple-icon-60x60.png') }}">
    <link rel="apple-touch-icon" sizes="72x72" href="{{ asset('img/favicon/apple-icon-72x72.png') }}">
    <link rel="apple-touch-icon" sizes="76x76" href="{{ asset('img/favicon/apple-icon-76x76.png') }}">
    <link rel="apple-touch-icon" sizes="114x114" href="{{ asset('img/favicon/apple-icon-114x114.png') }}">
    <link rel="apple-touch-icon" sizes="120x120" href="{{ asset('img/favicon/apple-icon-120x120.png') }}">
    <link rel="apple-touch-icon" sizes="144x144" href="{{ asset('img/favicon/apple-icon-144x144.png') }}">
    <link rel="apple-touch-icon" sizes="152x152" href="{{ asset('img/favicon/apple-icon-152x152.png') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('img/favicon/apple-icon-180x180.png') }}">
    <link rel="icon" type="image/png" sizes="192x192" href="{{ asset('img/favicon/android-icon-192x192.png') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('img/favicon/favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="96x96" href="{{ asset('img/favicon/favicon-96x96.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('img/favicon/favicon-16x16.png') }}">

    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css">

    <style>
        .bd-placeholder-img {
            font-size: 1.125rem;
            text-anchor: middle;
            -webkit-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
            user-select: none;
        }

        @media (min-width: 768px) {
            .bd-placeholder-img-lg {
                font-size: 3.5rem;
            }
        }

        .jumbotron {
            padding-top: 3rem;
            padding-bottom: 3rem;
            margin-bottom: 0;
            background-color: #fff;
            padding-left: 0 !important;
        }

        .jumbotron p:last-child {
            margin-bottom: 0;
        }

        .jumbotron h1 {
            font-weight: 300;
        }

        .jumbotron .container {
            max-width: 40rem;
        }

        footer {
            padding-top: 3rem;
            padding-bottom: 3rem;
        }

        footer p {
            margin-bottom: .25rem;
        }
    </style>
</head>
<body>
<main role="main">
    <section class="container">
        <div class="jumbotron">
            <h1>FORTE STORE</h1>

            <ul class="nav nav-pills mb-3" id="pills-tab" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" id="pills-shop-tab" data-toggle="pill" href="#shop" role="tab" aria-controls="shop" aria-selected="false">상점</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="pills-inventory-tab" data-toggle="pill" href="#inventory" role="tab" aria-controls="inventory" aria-selected="true">인벤토리</a>
                </li>
            </ul>
        </div>
    </section>

    <div class="album py-5">
        <div class="container">
            <div class="tab-content" id="pills-tabContent">
                <div class="tab-pane fade" id="inventory" role="tabpanel" aria-labelledby="pills-inventory-tab">
                    <div class="row">
                        @if(count($items) < 1)
                            <h1>아이템이 없습니다.</h1>
                        @endif

                        @foreach($items as $item)
                            <div class="col-md-4">
                                <div class="card mb-4 shadow-sm">
                                    <img class="card-img-top" src="{{ $item->item->image_url }}" alt="{{ $item->item->name }}">
                                    <div class="card-body">
                                        <h5 class="card-title">{{ $item->item->name }}</h5>
                                        <p class="card-text">
                                            @if($item->item->price > 0)
                                                <img src="{{ asset('img/forte-point.png') }}" style="width: 18px; margin-top: -4px" /> {{ $item->item->price }}
                                            @else
                                                이벤트 지급
                                            @endif
                                        </p>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <small class="text-muted">
                                            {{ isset($item->deleted_at) ? '철회: ' . $item->deleted_at->format('y년 m월 d일 H시 m분') : '구매: ' . $item->created_at->format('y년 m월 d일 H시 m분') }}
                                            </small>
                                            <div class="btn-group">
                                                @if($item->consumed > 0)
                                                    <button type="button" class="btn btn-sm btn-outline-secondary" disabled>사용됨</button>
                                                @elseif (date_diff(new \DateTime($item->created_at), new \DateTime())->format("%R%a") > 7)
                                                    <button type="button" class="btn btn-sm btn-outline-warning" disabled>
                                                        {{ date_diff(new \DateTime($item->created_at), new \DateTime())->format("%R%a")}} 일 지남
                                                    </button>
                                                @elseif ($item->deleted_at)
                                                    <button type="button" class="btn btn-sm btn-outline-danger" disabled>청약철회 완료</button>
                                                @else
                                                    <button id="btn-{{ $item->id }}" type="button" class="btn btn-sm btn-outline-success" onclick="withdraw({{ $item->id }})">청약철회</button>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="tab-pane fade show active" id="shop" role="tabpanel" aria-labelledby="pills-shop-tab">
                    <div class="row">
                        <embed type="text/html" src="{{ $redirect_url }}" style="top:0; left:0; width: 100%; height:550px;">
{{--                        <iframe src="{{ $redirect_url }}" frameborder="0" allowfullscreen style="position:absolute; top:0; left:0; width: 100%; height:100%;" />--}}
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<footer class="text-muted">
    <div class="container">
        <p class="float-right">
            <a href="#">Back to top</a>
        </p>
        <img style="width: 15rem; padding-bottom: 10px;" src="https://team-crescendo.me/wp-content/uploads/2019/07/Blue@2x.png">
        <p>© 2017-2020 Team Crescendo. All Right Reserved.</p>
    </div>
</footer>

<script src="//code.jquery.com/jquery-1.11.0.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js"></script>

<script>
    function withdraw(id) {
        $.ajax({
            type:'POST',
            url:'/withdraw',
            dataType: 'json',
            data: {
                'id': id,
            },
            headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
            success:function(data){
                console.log(data);
                $('#btn-' + id).text('청약철회 완료');
                $('#btn-' + id).removeClass('btn-outline-success');
                $('#btn-' + id).addClass('btn-outline-danger');
                $('#btn-' + id).prop('disabled', true);
                $('#btn-' + id).attr('onclick', '');
            }, error: function() {
                alert('다시 시도해주세요.');
            }
        });
    }
</script>
</body>
</html>
