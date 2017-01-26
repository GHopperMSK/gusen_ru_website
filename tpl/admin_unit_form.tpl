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

<style>
    #uploaded_images {
        background-color: #d5ddea;
    }
    
    #uploaded_images div {
        display: inline-block;
        border: 1px solid #000000;
        margin: 5px 5px 5px 5px;
        cursor: move;
        vertical-align: top;
    }
    
    #uploaded_images .load_progress {
        cursor: wait;
        text-align: center;
        width: 190px;
        height: 190px;
    }
    
    #uploaded_images div.over {
        border: 1px dashed #000;
    }
    
    #uploaded_images > #del_img {
        background-color: #a8abaf;
        text-align: center;
        vertical-align: middle;
        margin: auto;
        height: 30px;
        line-height: 30px; 
        cursor: default;
    }
</style>
    
    <script>
    
		$(function() {
			$('#fdistrict').on('change', function() {
				$("body").css("cursor", "progress");
				var curVal = $(this).find("option:selected").val();
				fillCity(curVal).then(
					function (resolve) {
						$("body").css("cursor", "default");
					    $('#city').html('');
					    $('#city').append("<option value='0'>Select a city</option>");
					    try {
					    	var cities = JSON.parse(resolve);
						    for(i=0;i<cities.length;i++) {
						    	$('#city').append("<option value='"+
						    		cities[i].id+"'>"+cities[i].name+
						    		"</option>");
						    }
						    $('#city').selectpicker('refresh');
					    }
					    catch(e) {
					    	console.log('Parse error: ');
					    }
					},
					function (reject) {
						$("body").css("cursor", "default");
						console.log('Promise reject');
						console.log(reject);
					}
				);
		  	});
		});
		
	    function getImages() {
	        // if images still loading
	        if (document.getElementsByName("load_progress").length > 0) {
	            alert('Not all images have loaded yet!');
	            return false;
	        }
	        
	        var aKeysNotZero = ["category", "fdistrict", "city", "manufacturer"];
	        for (i=0; i<aKeysNotZero.length; i++) {
	            if (document.getElementById(aKeysNotZero[i]).value == 0) {
	                alert("Please, fill all the fields!");
	                return false;
	            }
	        }
	        
	        var unitForm = document.getElementById("unitForm");
	        for (i=0; i<ui.childNodes.length; i++) {
	            if (ui.childNodes[i]) {
	                if ((ui.childNodes[i].tagName == 'DIV') &&
	                        (ui.childNodes[i] != di)) {
	                    var imgSrc = ui.childNodes[i].childNodes[0].getAttribute("name");
	                    var input = document.createElement("input");
	                    input.setAttribute("type", "hidden");
	                    input.setAttribute("name", "images[]");
	                    input.setAttribute("value", imgSrc);
	                    unitForm.appendChild(input);
	                }
	            }
	        }
	    }
	    
	    function delImages() {
	        var aDel = [];
	        var totalNodes = ui.childNodes.length;
	        for (i=0; i<totalNodes; i++) {
	            if (ui.childNodes[i]) {
	                if ((ui.childNodes[i].tagName == 'DIV') &&
	                        (ui.childNodes[i] != di)) {
	                    aDel.push(ui.childNodes[i]);
	                }
	            }
	        }
	        for (i=0; i<aDel.length; i++) {
	            ui.removeChild(aDel[i]);
	        }        
	        document.getElementById("afile").value = "";
	    }

		var aCities = [];
		aCities[0] = JSON.stringify({}); // if district isn't choosen
		const url_prefix = '/?page=ajax&ajax_mode=city&fdid=';
		function fillCity(fdid) {
			// TODO: save catched city list
	    	const url = url_prefix+fdid;
	    	
			// Return a new promise.
			return new Promise((resolve, reject) => {
				// Do the usual XHR stuff
				if (aCities[fdid]) {//fdid == 0) {
					// return empty json-string
					//resolve(JSON.stringify({}));
					resolve(aCities[fdid]);
				}
				else {
    				var req = new XMLHttpRequest();
					req.open('GET', url);
					
					req.onload = function() {
						// This is called even on 404 etc
						// so check the status
						if (req.status == 200) {
							// Resolve the promise with the response text
							aCities[fdid] = req.response;
							resolve(req.response);
						}
						else {
							// Otherwise reject with the status text
							// which will hopefully be a meaningful error
							reject(Error(req.statusText));
						}
					};
					
					// Handle network errors
					req.onerror = () => {
						reject(Error("Network Error"));
					};
					
					// Make the request
					req.send();
				}
			});
		}
	    
	    window.onload = function() {
	        // avoid XSLT bug which returns <textarea />
	        var descr = document.getElementById('description');
	        if (descr.value == 'Description') {
	            descr.value = '';
	        }
	        
	       ui = document.getElementById("uploaded_images");
	        var aImages = document.getElementsByName("available_images[]");
	        for (i=0; i<aImages.length; i++) {
	            var div = document.createElement('div');
	            var image = document.createElement('img');
	            image.src = '/images/tmb/'+aImages[i].value;
	            image.setAttribute("name", aImages[i].value);
	            div.appendChild(image);
	            div.setAttribute("draggable", "true");
	            ui.appendChild(div);
	            div.addEventListener('dragstart', handleDragStart, false);
	            div.addEventListener('dragenter', handleDragEnter, false);
	            div.addEventListener('dragover', handleDragOver, false);
	            div.addEventListener('dragleave', handleDragLeave, false);
	            div.addEventListener('drop', handleDrop, false);
	            div.addEventListener('dragend', handleDragEnd, false);                                    
	        }
	        
	        di = document.getElementById("del_img");
	        di.addEventListener('dragenter', handleDragEnter, false);
	        di.addEventListener('dragover', handleDragOver, false);
	        di.addEventListener('dragleave', handleDragLeave, false);
	        di.addEventListener('drop', handleDrop, false);
	    
	        document.getElementById('afile').addEventListener('change', function(e) {
	            for (i = 0; i < this.files.length; i++) { 
	                var fd = new FormData();
	                fd.append("afile", this.files[i]);
	                var xhr = new XMLHttpRequest();
	                xhr.open('POST', '?page=ajax&ajax_mode=image_load', true);
	
	                let prog = document.createElement('DIV');
	                prog.setAttribute("class", "load_progress");
	                prog.setAttribute("name", "load_progress");
	                prog.innerHTML = 'Loading...';
	                ui.appendChild(prog);
	                
	                xhr.upload.onprogress = function(e) {
	                    if (e.lengthComputable) {
	                        var percentComplete = Math.round((e.loaded / e.total) * 100);
	                        console.log(percentComplete + '% uploaded');
	                        prog.innerHTML = percentComplete + '%';
	                    }
	                };
	                xhr.onload = function() {
	                    if (this.status == 200) {
	                        var resp = JSON.parse(this.response);
	                        console.log('Server got:', resp);
	                        prog.removeAttribute("class");
	                        prog.removeAttribute("name");
	                        prog.innerHTML = '';
	                        var image = document.createElement('img');
	                        image.src = resp.dataUrl;
	                        image.setAttribute("name", resp.name);
	                        prog.appendChild(image);
	                        prog.setAttribute("draggable", "true");
	                        //ui.appendChild(prog);
	                        
	                        prog.addEventListener('dragstart', handleDragStart, false);
	                        prog.addEventListener('dragenter', handleDragEnter, false);
	                        prog.addEventListener('dragover', handleDragOver, false);
	                        prog.addEventListener('dragleave', handleDragLeave, false);
	                        prog.addEventListener('drop', handleDrop, false);
	                        prog.addEventListener('dragend', handleDragEnd, false);                        
	                    };
	                };
	                xhr.send(fd);
	            }
	        }, false);
	        
	    };
	
	    var dragSrcEl = null;
	    var di = null;
	    var ui = null;
	
	    function handleDragStart(e) {
	      dragSrcEl = this;
	      this.style.opacity = '0.4';  // this / e.target is the source node.
	    
	      e.dataTransfer.effectAllowed = 'move';
	      e.dataTransfer.setData('text/html', this.innerHTML);      
	    }    
	
	    function handleDragEnter(e) {
	        // this / e.target is the current hover target.
	        this.classList.add('over');
	    }
	    
	    function handleDragOver(e) {
	        if (e.preventDefault) {
	            e.preventDefault(); // Necessary. Allows us to drop.
	        }
	        
	        e.dataTransfer.dropEffect = 'move';  // See the section on the DataTransfer object.
	        
	        return false;
	    }
	
	    function handleDragLeave(e) {
	        this.classList.remove('over');  // this / e.target is previous target element.
	    }
	
	    function handleDrop(e) {
	        // this / e.target is current target element.
	        if (e.stopPropagation) {
	            e.stopPropagation(); // stops the browser from redirecting.
	        }
	        
	        // Don't do anything if dropping the same column we're dragging.
	        if (dragSrcEl != this) {
	            if(this != di) {
	                // Set the source column's HTML to the HTML of the column we dropped on.
	                dragSrcEl.innerHTML = this.innerHTML;
	                this.innerHTML = e.dataTransfer.getData('text/html');
	                this.classList.remove('over');
	                dragSrcEl.classList.remove('over');
	                dragSrcEl.style.opacity = '1';
	            }
	            else {
	                ui.removeChild(dragSrcEl);
	                this.classList.remove('over');
	            }
	        }
	
	        // See the section on the DataTransfer object.    
	        return false;
	    }
	
	    function handleDragEnd(e) {
	        this.style.opacity = '1';
	        this.classList.remove('over');
	    } 
    
    </script>
</head>
<body>

<nav class="navbar navbar-inverse  navbar-static-top">
	<div class="container-fluid">
    	<div class="navbar-header">
      		<a class="navbar-brand" href="/?page=admin">GusenRu</a>
    	</div>
    <ul class="nav navbar-nav">
     	<li><a class="" href="/?page=admin&act=unapproved_comments">Comments (%{comments_unapproved_total&null&0}%)</a></li>
      	<li class="active"><a class="" href="/?page=admin&act=admin_unit_form">Add new unit</a></li>
    </ul>
    <ul class="nav navbar-nav navbar-right">
    	<li><a href="/?page=admin&act=logout&msg=Successfully_Logged_out"><span class="glyphicon glyphicon-log-in"></span> Exit</a></li>
    </ul>    
  </div>
</nav>

<div class="col-lg-12">
%{admin_unit_form&admin_unit_form.xsl&0}%
</div>

<div class="clearfix"></div>
<br /><br />

</body>
</html>
