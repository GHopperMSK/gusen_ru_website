<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html lang="ru">
<head>
<meta charset="utf-8" />
<title>Агенство спецтехники Гусеница</title>
<style>
    FORM LABEL {
        width: 80px;
    }
    
    FIELDSET SELECT,INPUT,TEXTAREA {
        width: 100%;
    }
    
    DIV + P + P {
        text-align: right;

    }

    DIV + P + P INPUT {
        width: 100px;

    }
    
    #uploaded_images {
        background-color: #d5ddea;
    }
    
    #uploaded_images div {
        display: inline-block;
        border: 1px solid #000000;
        margin: 5px 5px 5px 5px;
        cursor: move;
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
        display: block;
        background-color: #a8abaf;
        height: 20px;
        cursor: default;
    }
</style>
<script>
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

    function fillCity(fdid) {
        if (fdid == 0) {
            document.getElementById("city").innerHTML = "<option value='0'>Seelct a city</option>";
            return;
        } else { 
            if (window.XMLHttpRequest) {
                // code for IE7+, Firefox, Chrome, Opera, Safari
                xmlhttp = new XMLHttpRequest();
            } else {
                // code for IE6, IE5
                xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
            }
            xmlhttp.onreadystatechange = function() {
                if (this.readyState == 4 && this.status == 200) {
                    var cityElement = document.getElementById("city");
                    var cities = JSON.parse(this.responseText);
                    cityElement.innerHTML = "<option value='0'>Seelct a city</option>";

                    for(i=0;i<cities.length;i++) {
                        var option = document.createElement("OPTION");
                        option.value = cities[i].id;
                        option.text = cities[i].name;
                        cityElement.add(option);
                    }                    
                }
            };
            
            xmlhttp.open("GET","?page=ajax&ajax_mode=city&fdid="+fdid,true);
            xmlhttp.send();
        }
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

<fieldset>
<legend>Unit editing</legend>
%{admin_unit_form&admin_unit_form.xsl&0}%
</fieldset>

</body>
</html>