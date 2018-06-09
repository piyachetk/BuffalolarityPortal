<!doctype html>
<html lang="{{ config('app.locale') }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link type="text/css" rel="stylesheet" href="/css/materialize.min.css"  media="screen,projection"/>
    <link type="text/css" rel="stylesheet" href="/fonts/thsarabunnew.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title') - Buffalolarity</title>

    <style>
        body {
            display: flex;
            min-height: 100vh;
            flex-direction: column;
            background-color: rgb(220, 220, 220);
        }
        main {
            flex: 1 0 auto;
        }

        strong{
            color: red;
            font-size: small;
        }

        .externalLinkImg{
            width: 32px;
            height: 32px;
            margin-right: 5px;
        }
    </style>
</head>
<body>
<nav class="black" role="navigation">
    <div class="nav-wrapper container">
        <a id="logo-container" href="/" class="brand-logo white-text" style="font-size: 2rem;">Buffalolarity</a>
        <ul class="right hide-on-med-and-down">
            <li><a href="/projects/">Projects</a></li>
        </ul>

        <ul id="nav-mobile" class="side-nav">
            <li><a class="center" href="/projects/">Projects</a></li>
        </ul>
        <a href="#" data-activates="nav-mobile" class="button-collapse"><i class="material-icons">menu</i></a>
    </div>
</nav>

@yield('pre-content')

<main class="container">
    @yield('content')
</main>

<footer class="page-footer black">
    <div class="container white-text" style="padding-bottom:1rem;">
        <a href="mailto:buffalolarity@buffalolarity.com">
            <img class="externalLinkImg" src="/img/mail.png" />
        </a>
        <a href="mailto:buffalolarity.th@gmail.com">
            <img class="externalLinkImg" src="/img/mail.png" />
        </a>
        <a href="https://buftaku.buffalolarity.com/">
            <img class="externalLinkImg" src="/img/buftaku_logo_round.png" />
        </a>
        <a href="https://www.facebook.com/buffalolarity">
            <img class="externalLinkImg" src="/img/facebook.png" />
        </a>
        <br/>
    </div>
    <div class="footer-copyright">
        <div class="container">
            <!--span class="hide-on-print"></span-->
            <span class="hide-on-screen">Copyright © 2017 Buffalolarity</span>
        </div>
    </div>
</footer>

<script src="{{ asset('js/app.js') }}"></script>
<script type="text/javascript" src="/js/jquery-3.2.1.min.js"></script>
<script type="text/javascript" src="/js/materialize.min.js"></script>
<script>
    $(document).ready(function(){
        $(".button-collapse").sideNav();});
</script>
</body>
</html>