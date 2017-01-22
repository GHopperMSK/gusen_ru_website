<!DOCTYPE html>
<html lang="ru-Ru">
<head>
	<link rel="apple-touch-icon" sizes="57x57" href="/apple-icon-57x57.png">
	<link rel="apple-touch-icon" sizes="60x60" href="/apple-icon-60x60.png">
	<link rel="apple-touch-icon" sizes="72x72" href="/apple-icon-72x72.png">
	<link rel="apple-touch-icon" sizes="76x76" href="/apple-icon-76x76.png">
	<link rel="apple-touch-icon" sizes="114x114" href="/apple-icon-114x114.png">
	<link rel="apple-touch-icon" sizes="120x120" href="/apple-icon-120x120.png">
	<link rel="apple-touch-icon" sizes="144x144" href="/apple-icon-144x144.png">
	<link rel="apple-touch-icon" sizes="152x152" href="/apple-icon-152x152.png">
	<link rel="apple-touch-icon" sizes="180x180" href="/apple-icon-180x180.png">
	<link rel="icon" type="image/png" sizes="192x192"  href="/android-icon-192x192.png">
	<link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
	<link rel="icon" type="image/png" sizes="96x96" href="/favicon-96x96.png">
	<link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
	<link rel="manifest" href="/manifest.json">
	<meta name="msapplication-TileColor" content="#ffffff">
	<meta name="msapplication-TileImage" content="/ms-icon-144x144.png">
	<meta name="theme-color" content="#ffffff">

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Агентство спецтехники Гусеница. %{description&null&10&page_unit}%">
    <meta name="keywords" content="Гусеничные экскаваторы,Колесные экскаваторы,Экскаваторы-погрузчики,Фронтальные погрузчики,Автогрейдеры,Бульдозеры,Буровые машины ,Мини-погрузчики,Мини-погрузчики сочлененные,Мини-экскаваторы,Мини-самосвалы,Асфальтоукладчики,Асфальтовые катки,Грунтовые катки,Дорожные фрезы,Оборудование для ямочного ремонта,Виброплиты,Автогудронаторы,Заливщики швов,Дробилки,Грохоты,Самосвалы сочлененные,Самосвалы рамные,Экскаваторы карьерные,Конвейеры ленточные,Автокраны,Автовышки,Башенные краны,Манипуляторы КМУ,Телескопические погрузчики,Гусеничные краны,Перегружатели,Трубоукладчики,Подъемники ножничные,Подъемники телескопические,Подъемники коленчатые,Асфальтобетонные заводы АБЗ,Бетонные заводы,Автобетононасосы,Автобетоносмесители,Растворонасосы,Вибропрессы,Стационарные бетононасосы,Вилочные погрузчики,Электрические погрузчики,Штабелёры (ричтраки),Самоходные тележки,Тележки гидравлические ручные,Боковые погрузчики,Электрокары,Малотоннажные грузовики,Среднетоннажные грузовики,Прицепы (полуприцепы),Тяжелые грузовики,Самосвалы грузовые,Гусеничные самосвалы,Топливозаправщики,Молоковозы,Земснаряды,Автоцистерны,Мусоровозы,Снегоуборочная техника,Поливомоечные машины,Ассенизаторские машины,Илососы">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/2.2.0/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
    <script src="/js/jquery.fs.selecter.min.js"></script>
    <link rel="stylesheet" href="/css/jquery.fs.selecter.css">
    <link rel="stylesheet" href="/css/gusen.css">

    <link  href="https://cdnjs.cloudflare.com/ajax/libs/fotorama/4.6.4/fotorama.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/fotorama/4.6.4/fotorama.js"></script>

    <title>%{title&null&10&page_unit}%</title>
    <script>
        function answer(com_id,user_id) {
            //console.log(aUsr);
            user_id = user_id.trim();
            if ($('#comment_form').length) {
                if (!aUsr[user_id]) {
                    aUsr[user_id] = $('SPAN[data-user_id=fb'+user_id+']')[0].textContent;
                }
                $('#p_com_id').remove();
                $('#comment_form').append('<input id="p_com_id" name="p_com_id" type="hidden" value="'+com_id+'"></input>');
                $('#resp').html('ответ на сообщение пользователя '+aUsr[user_id]+' <a href="#a" onclick="answer_cease()">(отмена)</a>');
                $('#user_comment').focus().select();
            }
            else {
                alert('Для отправки сообщений необходимо авторизироваться!');
            }
            return false;
        }
        
        function answer_cease() {
            $('#p_com_id').remove();
            $('#resp').html('');
            return false;
        }
        
        let aUsr = []; // array of users who commented the unit
        
        $(document).ready(function(){
            $('.fotorama').on('fotorama:fullscreenenter', function() {
                $(this).data('fotorama').setOptions({fit: 'contain'});
            });
            
            $('.fotorama').on('fotorama:fullscreenexit', function() {
                $(this).data('fotorama').setOptions({fit: 'cover'});
            });    

            $('select').selecter();

            var text_message = 'Можете оставить комментарий или задать вопрос...';
            var text_area = $('#text_comment');
            var com_max_len = 5000;

            // solve xslt an empty tag bug
            $(text_area).val('');

            $(text_area).keyup(function () {
                var len = $(this).val().length;
                if (len >= com_max_len) {
                    $(this).val($(this).val().substring(0, com_max_len));
                }
                var char = Math.max(0, com_max_len - len);
                $('#charNum').text(char+'/5000');
                
            });
        
            $('#users_list').each(function() {
                var aUsers = $(this).val().split(';');
                for (i=0; i<aUsers.length; i++) {
                    aUsers[i] = aUsers[i].trim();
                    if (aUsers[i]) {
                        let aUser = aUsers[i].split(':');
                        if (aUser[0] == 'vk') {
                            $.ajax({
                                url : "https://api.vk.com/method/users.get?user_ids="+aUser[1]+"&fields=first_name,last_name,photo_50&v={VK_VERSION}",
                                type : "GET",
                                dataType : "jsonp",
                                success : function(msg) {
                                    aUsr[aUser[1]] = msg.response[0].first_name + 
                                        ' ' + msg.response[0].last_name;

                                    $('SPAN[data-user_id='+aUser[0]+aUser[1]+']').each(function() {
                                        $(this).text(aUsr[aUser[1]]);
                                    });
                                    $('IMG[data-user_id='+aUser[0]+aUser[1]+']').each(function() {
                                        $(this).attr('src', msg.response[0].photo_50);
                                    });
                                }
                            });
                        }
                        else if (aUser[0] == 'fb') {
                            $.ajax({
                                url : "https://graph.facebook.com/"+aUser[1]+"/picture?width=50&height=50&redirect=false",
                                type : "GET",
                                dataType : "jsonp",
                                success : function(msg) {
                                    $('IMG[data-user_id='+aUser[0]+aUser[1]+']').each(function() {
                                        $(this).attr('src', msg.data.url);
                                    });
                                }
                            });
                            
                        }
                        else if (aUser[0] == 'gl') {
                            $.ajax({
                                url : "https://people.googleapis.com/v1/people/"+aUser[1]+"?requestMask.includeField=person.photos%2Cperson.names&fields=addresses%2CageRange%2Cbiographies%2Cbirthdays%2CbraggingRights%2CcoverPhotos%2CemailAddresses%2Cetag%2Cevents%2Cgenders%2CimClients%2Cinterests%2Clocales%2Cmemberships%2Cmetadata%2Cnames%2Cnicknames%2Coccupations%2Corganizations%2CphoneNumbers%2Cphotos%2Crelations%2CrelationshipInterests%2CrelationshipStatuses%2Cresidences%2CresourceName%2Cskills%2Ctaglines%2Curls&key=AIzaSyA7Cre392NBsUD8AC1vzlwkqMUyfBzXJAA",
                                type : "GET",
                                dataType : "jsonp",
                                success : function(msg) {
                                    if (!msg.error) {
                                        aUsr[aUser[1]] = msg.names[0].displayName;
                                        
                                        $('SPAN[data-user_id='+aUser[0]+aUser[1]+']').each(function() {
                                            $(this).text(msg.names[0].displayName);
                                        });
                                        $('IMG[data-user_id='+aUser[0]+aUser[1]+']').each(function() {
                                            $(this).attr('src', msg.photos[0].url+'?sz=50');
                                        });
                                    }
                                }
                            });
                        }
                    }
                }
                
            });                 
            
        });
    </script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/modernizr/2.8.3/modernizr.min.js"></script>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" />
    <link href="https://fonts.googleapis.com/css?family=Roboto+Condensed:400,400i,700,700i%7CRoboto:400,400i,700,700i" rel="stylesheet" />
</head>
<body>
    <div class="container">
       
        <div class="header">
            <a href="/" class="svglogo">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 366 72">
                <circle cx="44.2" cy="32.9" r="8.6" />
                <path d="M19.8 39.8c5.3 0 9.7 4.3 9.7 9.7s-4.3 9.7-9.7 9.7c-5.3 0-9.7-4.3-9.7-9.7s4.4-9.7 9.7-9.7m0-5c-8.1 0-14.7 6.6-14.7 14.7s6.6 14.7 14.7 14.7 14.7-6.6 14.7-14.7-6.6-14.7-14.7-14.7z" />
                <path d="M40.2 9.8C25.3 9.8 13.3 21.7 13 36.5c1.6-.8 3.3-1.4 5.1-1.6 1.1-11.3 10.5-20.1 22.1-20.1 12.2 0 22.2 9.9 22.2 22.2s-9.9 22.2-22.2 22.2c-2.9 0-5.6-.6-8.1-1.5-1 1.5-2.3 2.8-3.7 3.8 3.6 1.7 7.6 2.7 11.9 2.7 15 0 27.2-12.2 27.2-27.2S55.2 9.8 40.2 9.8z" />
                <circle class="st0" cx="19.8" cy="49.5" r="7" />
                <path d="M87.8 41.3h2.7L95 16.6h20.1l.8-2.7H92.7zM146.5 13.9c-3.4 4.1-5.8 7.2-8.8 10.8-2.2 2.8-4.3 5.5-6.2 7.8l-8.8-18.7h-3l9.9 20.8c-.6.8-1.3 1.7-2 2.7-.7.9-1.7 1.3-2.8 1.3h-2.4v2.7h3.5c1 0 2-.5 2.7-1.2 3-3.5 6.5-7.8 11.2-13.7 2.9-3.6 6.8-8.4 10.1-12.5h-3.4zM175.2 13.6c-5.5 0-9.5 0-12.5.1h-3.6c-3.3 0-7.3 2.4-8.3 7.7-.2.8-.3 1.6-.5 2.4-1 4.9-2.2 10.5-1 13.7 1.6 4.1 3.9 4.2 8.5 4.2h14.5c1.9 0 5.1-1.3 6.9-3.4 1.1-1.2 1.7-2.8 1.6-4.2h-2.7c0 1.1-.4 1.8-1 2.5-1.2 1.3-3.7 2.4-4.8 2.4H158c-4.8 0-5.2-.1-6.1-2.4-.9-2.5.2-8.1 1.1-12.2.2-.9.4-1.7.5-2.5.9-4.3 4.1-5.5 5.7-5.5h3.6c3 0 7-.1 12.5-.1 2.4 0 4 .5 4.8 1.6.9 1.2.5 2.8.5 2.8h2.7v-.1c.1-1 .1-2.8-1-4.3-1.4-1.8-3.8-2.7-7.1-2.7zM184.9 41.3h24l.5-2.7-21.3.2 1.9-10.1h20.5l.5-2.7h-20.5l1.8-9.5 21.3.1.5-2.7H190zM247.3 13.9l-2.4 12.4h-24.8l2.4-12.4h-2.7l-5.3 27.4h2.7l2.4-12.3h24.8L242 41.3h2.7l5.3-27.4zM280.5 13.9l-25.3 24.7h-1.1l4.5-24.7h-2.7l-5 27.4h5.4L282 16.2h1.4l-5.1 25.1h2.8l5.5-27.4zM321.4 13.9h-2.7l-4.3 24.7h-24.6l4.5-24.7h-2.8l-4.9 27.4h29.6l-1.4 6.7h2.7l2.1-9.4h-2.5zM357 41.3l-9.7-28.2h-3.6l-20.2 28.2h3.3l5-7v.1h19.9l2.4 6.9h2.9zm-23.3-9.6l11.4-15.9h.3l5.5 15.9h-17.2z" />
                <path class="st1" d="M88.6 54.5h2.6l.1-.7c.2-1.3-.2-2-1.8-2-.9 0-1.4.2-2.1.8-.2.2-.4.2-.6.2-.1 0-.3-.1-.4-.2-.1-.1-.1-.2-.1-.4.1-.3.4-.6 1-1 .7-.4 1.4-.6 2.3-.6 2.3 0 3.2 1 2.9 2.9l-1 5.1c-.1.3-.1.4-.2.6-.1.2-.3.3-.6.3s-.4-.1-.5-.3c-.1-.2-.1-.3 0-.6-.8.7-1.5.9-2.7.9-1.2 0-1.9-.2-2.3-.8-.3-.4-.5-1.1-.3-1.8.5-1.4 1.8-2.4 3.7-2.4zm-.5 4c.7 0 1.4-.1 2-.6.4-.3.6-.8.7-1.5l.2-.9h-2.5c-1.2 0-2 .5-2.2 1.5-.1 1 .4 1.5 1.8 1.5zM102.8 51.9c-.1.1-.3.1-.6.1h-3.5l-1.3 6.7c-.1.3-.1.4-.2.6-.1.2-.3.3-.6.3s-.4-.1-.5-.3c-.1-.2-.1-.3 0-.6l1.3-7.1c0-.2.1-.4.3-.6.2-.2.4-.2.6-.2h4c.3 0 .4 0 .5.1.1.1.2.3.2.5.1.2 0 .3-.2.5zM106.4 55.5c-.4 1.9.3 3 2 3 .7 0 1.4-.2 2-.6.3-.2.5-.3.7-.3.3 0 .5.3.4.6-.1.4-.7.8-1.1 1-.6.3-1.4.5-2.3.5-2.3 0-3.6-1.4-3-4.5.5-2.9 2.2-4.5 4.4-4.5s3.3 1.6 2.8 4c0 .2-.1.4-.3.6-.2.2-.4.2-.6.2h-5zm4.8-1c.1-.6.1-.9 0-1.4-.2-.8-.8-1.3-1.8-1.3-.9 0-1.8.5-2.3 1.3-.3.5-.4.8-.5 1.4h4.6zM117.6 55.6l-.6 3.1c-.1.3-.1.4-.2.6-.1.2-.3.3-.6.3s-.4-.1-.5-.3c-.1-.2-.1-.3 0-.6l1.3-7.1c.1-.3.1-.4.2-.6.1-.2.3-.3.6-.3s.4.1.5.3c.1.2.1.3 0 .6l-.5 2.8h4.2l.5-2.8c.1-.3.1-.4.2-.6.1-.2.3-.3.6-.3s.4.1.5.3c.1.2.1.3 0 .6l-1.3 7.1c-.1.3-.1.4-.2.6-.1.2-.3.3-.6.3s-.4-.1-.5-.3c-.1-.2-.1-.3 0-.6l.6-3.1h-4.2zM134 51.9c-.1.1-.3.1-.6.1h-2.2l-1.3 6.7c-.1.3-.1.4-.2.6-.1.2-.3.3-.6.3-.2 0-.4-.1-.5-.3-.1-.2-.1-.3 0-.6l1.3-6.7h-2.2c-.3 0-.4 0-.5-.1-.1-.1-.2-.3-.2-.5s.2-.4.3-.5c.1-.1.3-.1.6-.1h5.6c.3 0 .4 0 .5.1.1.1.2.3.2.5.1.2 0 .3-.2.5zM141.4 50.7c.9 0 1.5.2 2 .7.4.3.5.5.5.8 0 .1-.1.3-.3.4-.1.1-.3.2-.4.2-.2 0-.3-.1-.6-.3-.5-.5-.8-.6-1.4-.6-.9 0-1.6.3-2.2 1-.5.6-.8 1.2-1 2.3-.2 1.1-.2 1.8.1 2.3.3.6 1 1 1.8 1 .6 0 1-.1 1.7-.6.2-.2.5-.3.7-.3.2 0 .3.1.4.2.1.1.1.3.1.4-.1.3-.4.5-.8.8-.7.5-1.4.7-2.3.7-2.2 0-3.6-1.5-3-4.5.6-3 2.5-4.5 4.7-4.5zM153.8 51.9c-.1.1-.3.1-.6.1H151l-1.3 6.7c-.1.3-.1.4-.2.6-.1.2-.3.3-.6.3-.2 0-.4-.1-.5-.3-.1-.2-.1-.3 0-.6l1.3-6.7h-2.2c-.3 0-.4 0-.5-.1-.1-.1-.2-.3-.2-.5s.2-.4.3-.5c.1-.1.3-.1.6-.1h5.6c.3 0 .4 0 .5.1.1.1.2.3.2.5.1.2 0 .3-.2.5zM156.6 59.5c-.2 0-.4 0-.6-.2-.1-.2-.1-.4-.1-.6l1.3-7.1c0-.2.1-.4.3-.6.2-.2.4-.2.6-.2h3.3c1.5 0 2.4.9 2.1 2.3-.2.9-.8 1.6-1.7 1.9.9.4 1.3 1 1.1 2.1-.3 1.5-1.4 2.4-3.1 2.4h-3.2zm3.9-4h-2.7l-.5 2.9h2.7c1 0 1.7-.6 1.8-1.4.2-.9-.3-1.5-1.3-1.5zm.6-3.6h-2.6l-.5 2.6h2.6c1 0 1.6-.5 1.8-1.3s-.3-1.3-1.3-1.3zM173.7 51.7c.7.9.7 2.1.4 3.5-.3 1.3-.6 2.5-1.7 3.5-.7.6-1.6 1-2.7 1s-1.8-.4-2.3-1c-.7-.9-.7-2.1-.4-3.5.3-1.3.6-2.5 1.7-3.5.7-.6 1.6-1 2.7-1s1.8.4 2.3 1zm-2.1 6.1c.7-.6 1-1.7 1.2-2.7.2-1 .3-2.1-.2-2.7-.3-.4-.8-.6-1.5-.6-.6 0-1.2.2-1.7.6-.7.6-1 1.7-1.2 2.7-.2 1-.3 2.1.2 2.7.3.4.8.6 1.5.6s1.2-.2 1.7-.6zM188.5 50.7c.9 0 1.5.2 2 .7.4.3.5.5.5.8 0 .1-.1.3-.3.4-.1.1-.3.2-.4.2-.2 0-.3-.1-.6-.3-.5-.5-.8-.6-1.4-.6-.9 0-1.6.3-2.2 1-.5.6-.8 1.2-1 2.3-.2 1.1-.2 1.8.1 2.3.3.6 1 1 1.8 1 .6 0 1-.1 1.7-.6.2-.2.5-.3.7-.3.2 0 .3.1.4.2.1.1.1.3.1.4-.1.3-.4.5-.8.8-.7.5-1.4.7-2.3.7-2.2 0-3.6-1.5-3-4.5.6-3 2.5-4.5 4.7-4.5zM196.3 50.8h5.2c.2 0 .4 0 .6.2.1.2.1.4.1.6l-1.3 7.1c-.1.3-.1.4-.2.6-.1.2-.3.3-.6.3s-.4-.1-.5-.3c-.1-.2-.1-.3 0-.6l1.3-6.7h-4.2l-1.3 6.7c-.1.3-.1.4-.2.6-.1.2-.3.3-.6.3s-.4-.1-.5-.3c-.1-.2-.1-.3 0-.6l1.3-7.1c0-.2.1-.4.3-.6.1-.2.4-.2.6-.2zM206.8 55.5c-.4 1.9.3 3 2 3 .7 0 1.4-.2 2-.6.3-.2.5-.3.7-.3.3 0 .5.3.4.6-.1.4-.7.8-1.1 1-.6.3-1.4.5-2.3.5-2.3 0-3.6-1.4-3-4.5.5-2.9 2.2-4.5 4.4-4.5s3.3 1.6 2.8 4c0 .2-.1.4-.3.6-.2.2-.4.2-.6.2h-5zm4.8-1c.1-.6.1-.9 0-1.4-.2-.8-.8-1.3-1.8-1.3-.9 0-1.8.5-2.3 1.3-.3.5-.4.8-.5 1.4h4.6zM223.7 61.2c-.1.3-.1.4-.2.6s-.3.3-.6.3-.4-.1-.5-.3-.1-.3 0-.6l.3-1.7h-6c-.2 0-.4 0-.6-.2-.1-.2-.1-.4-.1-.6l1.3-7.1c.1-.3.1-.4.2-.6.1-.2.3-.3.6-.3s.4.1.5.3c.1.2.1.3 0 .6l-1.3 6.8h4.2l1.3-6.8c.1-.3.1-.4.2-.6.1-.2.3-.3.6-.3.2 0 .4.1.5.3.1.2.1.3 0 .6l-1.3 6.8h.5c.2 0 .4 0 .6.2.1.2.1.4.1.6l-.3 2zM234.8 51.9c-.1.1-.3.1-.6.1H232l-1.3 6.7c-.1.3-.1.4-.2.6-.1.2-.3.3-.6.3-.2 0-.4-.1-.5-.3-.1-.2-.1-.3 0-.6l1.3-6.7h-2.2c-.3 0-.4 0-.5-.1-.1-.1-.2-.3-.2-.5s.2-.4.3-.5c.1-.1.3-.1.6-.1h5.6c.3 0 .4 0 .5.1.1.1.2.3.2.5.1.2 0 .3-.2.5zM238.8 55.5c-.4 1.9.3 3 2 3 .7 0 1.4-.2 2-.6.3-.2.5-.3.7-.3.3 0 .5.3.4.6-.1.4-.7.8-1.1 1-.6.3-1.4.5-2.3.5-2.3 0-3.6-1.4-3-4.5.5-2.9 2.2-4.5 4.4-4.5s3.3 1.6 2.8 4c0 .2-.1.4-.3.6-.2.2-.4.2-.6.2h-5zm4.8-1c.1-.6.1-.9 0-1.4-.2-.8-.8-1.3-1.8-1.3-.9 0-1.8.5-2.3 1.3-.3.5-.4.8-.5 1.4h4.6zM248.9 59.1c-.3.3-.5.5-.7.5-.1 0-.2 0-.3-.1-.2-.1-.2-.3-.2-.5 0-.1.1-.3.3-.5l2.7-3.3-1.5-3.2c-.1-.2-.1-.4-.1-.5 0-.2.2-.4.4-.5.1-.1.3-.1.4-.1.3 0 .4.2.6.5l1.3 3 2.4-3c.3-.3.5-.5.7-.5.1 0 .2 0 .3.1.2.1.3.3.2.5 0 .2-.1.3-.3.5l-2.7 3.2 1.5 3.3c.1.2.1.4.1.5 0 .2-.2.4-.4.5-.1.1-.3.1-.4.1-.3 0-.4-.2-.6-.5l-1.3-3-2.4 3zM259.8 55.6l-.6 3.1c-.1.3-.1.4-.2.6-.1.2-.3.3-.6.3s-.4-.1-.5-.3c-.1-.2-.1-.3 0-.6l1.3-7.1c.1-.3.1-.4.2-.6.1-.2.3-.3.6-.3s.4.1.5.3c.1.2.1.3 0 .6l-.5 2.8h4.2l.5-2.8c.1-.3.1-.4.2-.6.1-.2.3-.3.6-.3s.4.1.5.3c.1.2.1.3 0 .6l-1.3 7.1c-.1.3-.1.4-.2.6-.1.2-.3.3-.6.3s-.4-.1-.5-.3c-.1-.2-.1-.3 0-.6l.6-3.1h-4.2zM270.8 59c-.3.4-.6.6-.9.6-.2 0-.4-.1-.5-.2-.1-.2-.2-.4-.1-.7l1.3-7.1c.1-.3.1-.4.2-.6.1-.2.3-.3.6-.3s.4.1.5.3c.1.2.1.3 0 .6l-1.1 5.9 5.3-6.1c.3-.4.6-.6.9-.6.2 0 .4.1.5.2.1.1.2.4.1.7l-1.3 7.1c-.1.3-.1.4-.2.6-.1.2-.3.3-.6.3-.2 0-.4-.1-.5-.3-.1-.2-.1-.3 0-.6l1.1-5.9-5.3 6.1zM282.9 54.4h.6l3.6-3.3c.3-.3.5-.4.7-.4.1 0 .3.1.3.1.1.1.2.2.1.4 0 .2-.1.3-.4.6l-3.3 3 2.4 3.5c.2.3.2.4.2.6 0 .2-.1.3-.3.4-.1.1-.3.1-.4.1-.2 0-.3-.1-.5-.4l-2.5-3.7h-.6l-.6 3.2c-.1.3-.1.4-.2.6-.1.2-.3.3-.6.3s-.4-.1-.5-.3c-.1-.2-.1-.3 0-.6l1.3-7.1c.1-.3.1-.4.2-.6.1-.2.3-.3.6-.3s.4.1.5.3c.1.2.1.3 0 .6l-.6 3zM292.2 59c-.3.4-.6.6-.9.6-.2 0-.4-.1-.5-.2-.1-.2-.2-.4-.1-.7l1.3-7.1c.1-.3.1-.4.2-.6.1-.2.3-.3.6-.3s.4.1.5.3c.1.2.1.3 0 .6l-1.1 5.9 5.3-6.1c.3-.4.6-.6.9-.6.2 0 .4.1.5.2.1.1.2.4.1.7l-1.3 7.1c-.1.3-.1.4-.2.6-.1.2-.3.3-.6.3-.2 0-.4-.1-.5-.3-.1-.2-.1-.3 0-.6l1.1-5.9-5.3 6.1z" />
                </svg>
            </a>
            <div class="head_argue"><span>Только мощные предложения!</span></div>
            <div class="infosection">
                <a class="phone" href="tel:+74993227688">+7 (499) 322-76-88</a>
                <a class="mail" href="mailto:info@gusen.ru">info@gusen.ru</a>
            </div>
        </div>

        <div class="content">
            %{unit_page_unit&unit_page_unit.xsl&10}%
        </div>
        <div class="line-w"></div>
        <ul class="media-list">
            %{unit_comments&unit_comments.xsl&0}%
            %{user&user_comment_form.xsl&0&comment_form}%
        </ul>
        
    </div>
    <div class="footer">
        <div class="container">
            <div class="row">
                <div class="fltlft">
                    <div class="footp1"><span>© ООО «Гусеница», 2016 - 2017</span></div>
                    <div class="footp2"><a href="/about"><h5>О компании</h5></a></div>   
                </div>
                <div class="fltrght">
                <div class="footp3"><a href="/copyright"><h5>Пользовательское соглашение</h5></a></div>
                <div class="mob">
                    <a target="_blank" href="https://vk.com/club137789409" class="footp4 socseti vk"><i class="fa fa-vk" aria-hidden="true"></i></a>
                </div>
                </div>
            </div>   
        </div>
    </div>
    <script>
        (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
        (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
        m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
        })(window,document,'script','https://www.google-analytics.com/analytics.js','ga');
        ga('create', 'UA-88906763-1', 'auto');
        ga('send', 'pageview');
    </script>
</body>
</html>