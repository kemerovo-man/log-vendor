<!DOCTYPE html>
<html>
<head>
    <title>Логи</title>
    <script
            src="https://code.jquery.com/jquery-2.2.4.min.js"
            integrity="sha256-BbhdlvQf/xTY9gja0Dq3HiwQF8LaCRTXxZKRutelT44="
            crossorigin="anonymous"></script>
    <style>
        .logs {
            display: none;
            padding-left: 10px;
        }

        .logs2 {
            display: none;
            padding-left: 20px;
        }
    </style>
    <script language="JavaScript">
      $(function () {
        $('.date').click(function () {
          $(this).next().slideToggle();
        });
        $('.first_word').click(function () {
          $(this).next().slideToggle();
        });
      });
    </script>
</head>
<body>

<h2>Логи</h2>
<br>
@foreach($dateLogs as $date=>$logs)
    <div class="date">
        <a href="#">
            {{$date}}
        </a>
    </div>
    <div class="logs">
        @if(config('log.collapseFirstWord'))
            @foreach($logs as $firstWord=>$firsWordLogs)
                @if($firstWord != 'ALL')
                    <div class="first_word">
                        <a href="#">
                            {{$firstWord}}
                        </a>
                    </div>
                    <div class="logs2">
                        @foreach($firsWordLogs as $log)
                            <a href="/logs/{{$log}}">{{$log}}</a><br>
                        @endforeach
                    </div>
                @endif
            @endforeach
        @else
            @foreach($logs['ALL'] as $log)
                <a href="/logs/{{$log}}">{{$log}}</a><br>
            @endforeach
        @endif
    </div>
@endforeach

</body>
</html>
