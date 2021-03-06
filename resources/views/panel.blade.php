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
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+KR:wght@400;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/7.33.1/sweetalert2.min.css" rel="stylesheet">
    <style>
        * {
            font-family: 'Noto Sans KR', sans-serif;
        }

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
            padding-bottom: 1rem;
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

        #title-outline {
            color: black;
            -webkit-text-fill-color: white; /* Will override color (regardless of order) */
            -webkit-text-stroke-width: 1px;
            -webkit-text-stroke-color: black;

            display: inline;
            font-weight: bolder;
        }

        #title {
            display: inline;
            font-weight: bolder;
        }

        .nav-pills .nav-link {
            color: black;
            opacity: 0.6;
        }

        .nav-pills .nav-link.active, .nav-pills .show > .nav-link {
            color: black;
            background-color: white;
            font-weight: bolder;
            opacity: 1;
        }
    </style>

</head>
<body>
<main role="main">
    <section class="container">
        <div class="jumbotron">
            <img style="width: 10rem; padding-bottom: 1.5rem;display: block"
                 src="https://team-crescendo.me/wp-content/uploads/2019/07/Blue@2x.png">
            <div class="row" style="width: 100vw">
                <div class="col-sm">
                    <h1 id="title-outline" class="mr-1">Forte</h1>
                    <h1 id="title">Store</h1>
                </div>
                <ul class="nav nav-pills mb-3 col-sm" id="pills-tab" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" id="pills-shop-tab" data-toggle="pill" href="#shop" role="tab"
                           aria-controls="shop" aria-selected="false">상점</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="pills-inventory-tab" data-toggle="pill" href="#inventory" role="tab"
                           aria-controls="inventory" aria-selected="true">인벤토리</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="pills-item-detail-tab"
                           href="https://cafe.naver.com/teamcrescendocafe/book5101938/1396" role="tab"
                           aria-controls="item-detail" aria-selected="true">아이템 상세 안내</a>
                    </li>
                </ul>
            </div>
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
                            <div class="col-md-4" id="i-{{$item->id}}">
                                <div class="card mb-4 shadow-sm">
                                    <img class="card-img-top" src="{{ $item->items->image_url }}"
                                         alt="{{ $item->items->name }}">
                                    <div class="card-body">
                                        <h5 class="card-title">{{ $item->items->name }}</h5>
                                        <p class="card-text">
                                            @if($item->items->price > 0)
                                                <img src="{{ asset('img/forte-point.png') }}"
                                                     style="width: 18px; margin-top: -4px"/> {{ $item->items->price }}
                                            @else
                                                이벤트 지급
                                            @endif
                                        </p>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <small class="text-muted">
                                                {{ isset($item->deleted_at) ? '철회: ' . $item->deleted_at : '구매: ' . $item->created_at }}
                                            </small>
                                            <div class="btn-group">
                                                @if($item->consumed > 0)
                                                    <button type="button" class="btn btn-sm btn-outline-secondary"
                                                            disabled>사용됨
                                                    </button>
                                                @elseif (date_diff(new \DateTime($item->created_at), new \DateTime())->format("%R%a") > 7)
                                                    <button type="button" class="btn btn-sm btn-outline-warning"
                                                            disabled>
                                                        {{ date_diff(new \DateTime($item->created_at), new \DateTime())->format("%R%a")}}
                                                        일 지남
                                                    </button>
                                                @elseif ($item->deleted_at)
                                                    <button type="button" class="btn btn-sm btn-outline-danger"
                                                            disabled>청약철회 완료
                                                    </button>
                                                @else
                                                    <button id="btn-{{ $item->id }}" type="button"
                                                            class="btn btn-sm btn-outline-success"
                                                            onclick="withdraw({{ $item->id }})">청약철회
                                                    </button>
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
                        <embed type="text/html" src="{{ $redirect_url }}"
                               style="top:0; left:0; width: 100%; height:550px;">
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<footer class="text-muted">
    <div class="container">
        <div id="text-13" class="widget widget_text">
            <div class="textwidget"><p style="line-height: 17px; font-size: 12px;">팀 크레센도<br>
                    대표자: 이정민 | skile@crsd.team | 010-2269-3816</p>
                <p style="text-align: left; line-height: 17px; font-size: 12px;"><a
                        href="https://team-crescendo.me/policy/bot-terms/">봇 이용약관</a>&nbsp; &nbsp; &nbsp;<strong><a
                            href="https://team-crescendo.me/policy/privacy/">개인정보취급방침</a></strong>&nbsp; &nbsp; &nbsp;<a
                        href="https://team-crescendo.me/email-security/">이메일 무단 수집 거부</a><br><a
                        href="https://discord.gg/DF3yxBS" target="_blank" rel="noopener noreferrer">공식 디스코드</a>&nbsp;
                    &nbsp; &nbsp;<a href="https://cafe.naver.com/teamcrescendocafe" target="_blank"
                                    rel="noopener noreferrer">공식카페</a></p>
                <p style="line-height: 17px; font-size: 12px;">© 2017-2020 Team Crescendo. All Right Reserved.</p>
            </div>
        </div>
    </div>
</footer>

<script src="//code.jquery.com/jquery-1.11.0.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/7.33.1/sweetalert2.min.js"></script>
<script>
    @if(isset($item->user_id))
    function withdraw(item_id) {
        Swal.fire({
            title: '청약철회',
            html: "구매하신 아이템의 청약철회는 구매 기간으로부터 7일이 지나지 않았고, 최종 수령을 받지 않은 아이템만 가능합니다.<br>자세한 사항은 <a href='https://cafe.naver.com/teamcrescendocafe/book5101938/759'>카페</a>를 참고하세요.<br><br>청약철회를 진행하려면 아래 버튼을 눌러주세요",
            type: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: '네',
            cancelButtonText: '아니요'
        }).then((result) => {
            if (result.value) {
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });
                $.post("/withdraw/" + item_id).done(function (response) {
                    Swal.fire({
                        title: '성공',
                        text: '청약철회가 완료되었습니다!',
                        type: 'success'
                    })
                    location.reload()
                }).fail(function (response) {
                    Swal.fire({
                        title: '안내',
                        text: response.responseJSON.message,
                        type: 'info'
                    })
                });
            }
        })
    }
    @endif
</script>
</body>
</html>
