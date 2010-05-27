{include file=header.html.tpl}
<h1>Login</h1>

{if $loginfailed}
<p class="error">Login fehlgeschlagen!</p>
{/if}

<form action="userpanel.php" method="post" class="login">
 <fieldset>
  <label for="username" class="username">Benutzername:</label>
  <input type="text" class="username" name="username" value="{if isset($smarty.request.username)}{$smarty.request.username|stripslashes|escape:html}{/if}" />
  <label for="password" class="password">Passwort:</label>
  <input type="password" class="password" name="password" />

  <input type="submit" class="submit" name="login" value="Login" />
 </fieldset>
</form>
{include file=footer.html.tpl}