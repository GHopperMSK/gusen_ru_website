<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html lang="ru">
<head>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1/jquery.js"></script>
<script src="/lightbox2/js/lightbox.js"></script>
<link href="/lightbox2/css/lightbox.css" rel="stylesheet" />
<link rel="stylesheet" type="text/css" media="screen and (min-device-width: 701px)" href="/css/main.css" />
<link rel="stylesheet" type="text/css" media="screen and (max-device-width: 700px)" href="/css/main_mobile.css" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<meta name="description" content="Агенство спецтехники Гусеница - только мощные предложения!" />
<meta name="keywords" content="Гусеничные экскаваторы,Колесные экскаваторы,Экскаваторы-погрузчики,Фронтальные погрузчики,Автогрейдеры,Бульдозеры,Буровые машины ,Мини-погрузчики,Мини-погрузчики сочлененные,Мини-экскаваторы,Мини-самосвалы,Асфальтоукладчики,Асфальтовые катки,Грунтовые катки,Дорожные фрезы,Оборудование для ямочного ремонта,Виброплиты,Автогудронаторы,Заливщики швов,Дробилки,Грохоты,Самосвалы сочлененные,Самосвалы рамные,Экскаваторы карьерные,Конвейеры ленточные,Автокраны,Автовышки,Башенные краны,Манипуляторы КМУ,Телескопические погрузчики,Гусеничные краны,Перегружатели,Трубоукладчики,Подъемники ножничные,Подъемники телескопические,Подъемники коленчатые,Асфальтобетонные заводы АБЗ,Бетонные заводы,Автобетононасосы,Автобетоносмесители,Растворонасосы,Вибропрессы,Стационарные бетононасосы,Вилочные погрузчики,Электрические погрузчики,Штабелёры (ричтраки),Самоходные тележки,Тележки гидравлические ручные,Боковые погрузчики,Электрокары,Малотоннажные грузовики,Среднетоннажные грузовики,Прицепы (полуприцепы),Тяжелые грузовики,Самосвалы грузовые,Гусеничные самосвалы,Топливозаправщики,Молоковозы,Земснаряды,Автоцистерны,Мусоровозы,Снегоуборочная техника,Поливомоечные машины,Ассенизаторские машины,Илососы" />
<meta charset="utf-8" />
<title>%{title&null&page_unit}%</title>
<script>

function answer(com_id,user_id) {
    if ($('#comment_form').length) {
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

var aUsr = []; // array of users who commented the unit

$( document ).ready( function(e) {
    var text_message = 'Можете оставить комментарий или задать вопрос...';
    var text_area = $('#text_comment');
    var com_max_len = 5000;

    $(text_area).val('');
    $(text_area).val(text_message);
    $(text_area).attr("class","empty_text");

    $('#text_comment').focusout( function() {
        var txt = $(this).val();
        if ((txt == '') && (txt != text_message)) {
            $(this).val(text_message);
            $(this).attr("class","empty_text");
        }
    });

    $('#text_comment').focus( function() {
        var txt = $(this).val();
        if (txt == text_message) {
            $(this).val('');
            $(this).removeAttr('class');
        }
    });
    
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
            if (aUsers[i].trim()) {
                let aUser = aUsers[i].split(':');
                // check social network
                if (aUser[0] == 'vk') {
                    $.ajax({
                        url : "https://api.vk.com/method/users.get?user_ids="+aUser[1]+"&fields=photo_50,city,verified&v=5.60",
                        type : "GET",
                        dataType : "jsonp",
                        success : function(msg) {
                            $('*[user_id='+aUser[0]+aUser[1]+']').each(function() {
                                var img = $("<IMG>").attr('src', msg.response[0].photo_50);
                                $(this).append(img);
                                $(this).append(' '+msg.response[0].first_name);
                            });
                        }
                    });
                }
                else if (aUser[0] == 'fb')
                {
                    $.ajax({
                        url : "https://graph.facebook.com/"+aUser[1]+"/picture?type=small",
                        type : "GET",
                        dataType : "jsonp",
                        success : function(msg) {
                            $('*[user_id='+aUser[0]+aUser[1]+']').each(function() {
                                var img = $("<IMG>").attr('src', msg.data.url);
                                var name = $(this).text();
                                $(this).text('');
                                $(this).append(img);
                                $(this).append(' '+name);
                                //$(this).append(' '+'unknown_name');
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
                            $('*[user_id='+aUser[0]+aUser[1]+']').each(function() {
                                var img = $("<IMG>").attr('src', msg.photos[0].url+'?sz=50');
                                $(this).append(img);
                                $(this).append(' '+msg.names[0].givenName);
                            });
                        }
                    });
                }
            }
        }
        
    });     
    
});

</script>
    
</head>

<body>
<script>
  (function(d, s, id){
     var js, fjs = d.getElementsByTagName(s)[0];
     if (d.getElementById(id)) {return;}
     js = d.createElement(s); js.id = id;
     js.src = "//connect.facebook.net/en_US/sdk.js";
     fjs.parentNode.insertBefore(js, fjs);
   }(document, 'script', 'facebook-jssdk'));
</script>

<script>
    (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
    (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
    m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
    })(window,document,'script','https://www.google-analytics.com/analytics.js','ga');
    
    ga('create', 'UA-88906763-1', 'auto');
    ga('send', 'pageview');

</script>
<section class="main_block header">
<h3>+7 (499) 322-76-88</h3>
<h3>info@gusen.ru</h3>
<a href="/"><h1>Агенство спецтехники ГУСЕНИЦА</h1></a>
<h4>Только мощные предложения!</h4>
</section>

<section class="main_block content">

%{unit_page_unit&unit_page_unit.xsl}%

<script>
    lightbox.option({
      'resizeDuration': 200,
      'wrapAround': true,
      'fadeDuration': 100,
      'imageFadeDuration': 100
    })
</script>

</section>
<section class="main_block user">
%{user&user.xsl}%
</section>
<section class="main_block comments" id="comments">
%{unit_comments&unit_comments.xsl}%
</section>
<section class="main_block footer"> 
© ООО «Гусеница», 2016<br />
<a href="/copyright">Пользовательское соглашение</a>
</section>
</body>
</html>