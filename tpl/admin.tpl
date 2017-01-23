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
    
	<script src="https://vk.com/js/api/openapi.js?137" type="text/javascript"></script>    
</head>
<body>
<script type="text/javascript">

VK.init({
	apiId: 5768859
});

function wallPost(id) {
console.log('wallPost');
	$("body").css("cursor", "progress");
	
	VK.Auth.login(function(response) {
		if (response.session) {
			console.log('auth ok');
			postUnit(id);
			if (response.settings) {
				console.log(response.settings);
			}
	  } else {
			console.log('auth failed');
	  }
	}, VK.access.PHOTOS | VK.access.WALL);
}

function postUnit(id) {
	VK.Api.call('photos.getWallUploadServer', {
	    	group_id: 137789409
	    }, function(r) {
	    	if (r.response) {
		    	console.log('uploadServer: '+r.response.upload_url);
		    	$.post('/?page=ajax&ajax_mode=vk_upload', {
	                    url: r.response.upload_url,
	                    unit_id: id
	                    //proccessData: false
	                }, function (data) {
	                	var p = JSON.parse(data);
	                	var unit = JSON.parse(p.unit);

						var message = unit.category+' / '+unit.manufacturer+' '+
							unit.name+', '+
							unit.year + " г.\n" + unit.fdistrict + ', ' +
							unit.region + ', г. ' + unit.city + "\n\n";
						message += unit.description;

						VK.Api.call('photos.saveWallPhoto', {
	                        group_id: 137789409,
	                        photo: p.photo,
	                        server: p.server,
	                        hash: p.hash
	                    	}, function (s) {
	                    		$("body").css("cursor", "default");
	                    		var attachments = '';
	                    		for (i=0; i<s.response.length; i++) {
	                    			attachments += s.response[i].id + ',';
	                    		}
	                    		attachments += 'https://gusen.ru/unit/'+id;
	                    		VK.Api.call('wall.post', {
		                    			owner_id: '-137789409',
		                    			message: message,
		                    			attachments: attachments
	                    			},
	                    			function(r) {
	                    				console.log('Wall.post: '+ r);
	                    			}
	                    		);
	                    	}
	                    )
	                }
	            )
	    	}
	    }
	)	
}

</script>

<nav class="navbar navbar-inverse navbar-static-top">
	<div class="container-fluid">
    	<div class="navbar-header">
      		<a class="navbar-brand" href="/?page=admin">GusenRu</a>
    	</div>
    <ul class="nav navbar-nav">
    	<li><a class="" href="/?page=admin&act=unapproved_comments">Comments (%{comments_unapproved_total&null&0}%)</a></li>
    	<li><a class="" href="/?page=admin&act=admin_unit_form">Add new unit</a></li>
    </ul>
    <ul class="nav navbar-nav navbar-right">
    	<li><a href="/?page=admin&act=logout&msg=Successfully_Logged_out"><span class="glyphicon glyphicon-log-in"></span> Exit</a></li>
    </ul>    
  </div>
</nav>

%{search_form&search_form.xsl&0&admin}%

%{unit_list&admin_unit_list.xsl&0}%

<div class="clearfix"></div>

<section class="paginator">
	%{unit_list_paginator&search_paginator.xsl&0&admin}%
</section>

</body>
</html>