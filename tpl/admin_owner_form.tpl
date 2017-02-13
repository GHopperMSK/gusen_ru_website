<!DOCTYPE html>
<html lang="ru-Ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Агентство спецтехники Гусеница - только мощные предложения!">
    <meta name="keywords" content="Гусеничные экскаваторы,Колесные экскаваторы,Экскаваторы-погрузчики,Фронтальные погрузчики,Автогрейдеры,Бульдозеры,Буровые машины ,Мини-погрузчики,Мини-погрузчики сочлененные,Мини-экскаваторы,Мини-самосвалы,Асфальтоукладчики,Асфальтовые катки,Грунтовые катки,Дорожные фрезы,Оборудование для ямочного ремонта,Виброплиты,Автогудронаторы,Заливщики швов,Дробилки,Грохоты,Самосвалы сочлененные,Самосвалы рамные,Экскаваторы карьерные,Конвейеры ленточные,Автокраны,Автовышки,Башенные краны,Манипуляторы КМУ,Телескопические погрузчики,Гусеничные краны,Перегружатели,Трубоукладчики,Подъемники ножничные,Подъемники телескопические,Подъемники коленчатые,Асфальтобетонные заводы АБЗ,Бетонные заводы,Автобетононасосы,Автобетоносмесители,Растворонасосы,Вибропрессы,Стационарные бетононасосы,Вилочные погрузчики,Электрические погрузчики,Штабелёры (ричтраки),Самоходные тележки,Тележки гидравлические ручные,Боковые погрузчики,Электрокары,Малотоннажные грузовики,Среднетоннажные грузовики,Прицепы (полуприцепы),Тяжелые грузовики,Самосвалы грузовые,Гусеничные самосвалы,Топливозаправщики,Молоковозы,Земснаряды,Автоцистерны,Мусоровозы,Снегоуборочная техника,Поливомоечные машины,Ассенизаторские машины,Илососы">

    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.12.1/css/bootstrap-select.min.css">

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/2.2.0/jquery.min.js"></script>

    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.12.1/js/bootstrap-select.min.js"></script>

    <link rel="stylesheet" href="/css/gusen.css">
    <title>Панель администратора - Агентство спецтехники Гусеница</title>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/modernizr/2.8.3/modernizr.min.js"></script>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" />
    <link href="https://fonts.googleapis.com/css?family=Roboto+Condensed:400,400i,700,700i%7CRoboto:400,400i,700,700i" rel="stylesheet" />
    
    <script>
	    window.onload = function() {
	        // avoid XSLT bug which returns <textarea />
	        var descr = document.getElementById('description');
	        if (descr.value == 'Description') {
	            descr.value = '';
	        }
	    }
    </script>
    
</head>
<body>

<nav class="navbar navbar-inverse navbar-static-top">
	<div class="container-fluid">
    	<div class="navbar-header">
      		<a class="navbar-brand" href="/?page=admin">GusenRu</a>
    	</div>
    <ul class="nav navbar-nav">
    	<li class="dropdown">
			<a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Units <span class="caret"></span></a>
			<ul class="dropdown-menu">
				<li><a href="/?page=admin">Units list</a></li>
				<li><a href="/?page=admin&act=admin_unit_form">Add Unit</a></li>
				<li><a href="/?page=admin&act=unit_arch_list">Archive</a></li>
			</ul>
    	</li>
    	<li class="dropdown">
			<a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Owners <span class="caret"></span></a>
			<ul class="dropdown-menu">
				<li><a href="/?page=admin&act=owners_list">Owners list</a></li>
				<li><a href="/?page=admin&act=owner_form">Add an Owner</a></li>
			</ul>
    	</li>
    	<li><a class="" href="/?page=admin&act=comments_unapproved_list">Comments (%{UnitMod&null&0&unapproved_count}%)</a></li>
    </ul>
    <ul class="nav navbar-nav navbar-right">
    	<li><a href="/?page=admin&act=logout&msg=Successfully_Logged_out"><span class="glyphicon glyphicon-log-out"></span> Exit</a></li>
    </ul>    
  </div>
</nav>

<div class="container-fluid">
%{OwnerMod&admin_owner_form.xsl&0&owner_form}%
</div>

<div class="clearfix"></div>
<br /><br />

</body>
</html>
