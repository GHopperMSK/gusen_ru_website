<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html lang="ru">
<head>
<meta charset="utf-8" />
<title>Агенство спецтехники Гусеница</title>
<style>
.unit IMG {
    float: left;
}

.unit {
    clear: both;
    margin-top: 10px;
    margin-bottom: 10px;
}

.paginator A {
    text-decoration: none;
}

</style>
</head>
<body>
<a href="?page=admin&act=main">Home</a>
<a href="?page=admin&act=logout&msg=Successfully_Logged_out">Log out</a>
<a href="?page=admin&act=admin_unit_form">Add an unit</a>
<a href="?page=admin&act=unapproved_comments">Comments (%{comments_unapproved_total&null}%)</a>
<br /><br />
%{search_form&search_form.xsl&admin}%

%{search_page_unit_list&admin_unit_list.xsl}%
<section class="paginator">
%{unit_list_paginator&search_paginator.xsl&admin}% 
</section>

</body>
</html>