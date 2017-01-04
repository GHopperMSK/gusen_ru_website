<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html lang="ru">
<head>
<meta charset="utf-8" />
<title>Агенство спецтехники Гусеница</title>
<style>
FIELDSET {
    margin-left: auto;
    margin-right: auto;
    width: 440px;
}

LABEL {
    width: 80px;
    float: left;    
}
LABEL+INPUT {
    width: 350px;
    float: left;
}

P {
    clear: left;
    height: 16px;
}

</style>
</head>
<body>
<form name="login" id="login" method="POST" action="?page=admin&act=check_login">
    <fieldset>
    <legend>Sign in</legend>
        <p>
            <label for="username">Username: </label>
            <input type="text" size="100" name="username" id="username" value="" />
        </p>
        <p>
            <label for="password">Password: </label>
            <input type="password" size="40" name="password" id="password" value="" />
        </p>
        <p>
            <input type="submit" value="Submit"/>
            <input type="reset" value="reset"/>
        </p>
    </fieldset>
</form>
</body>
</html>