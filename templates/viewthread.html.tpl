{include file=header.html.tpl}
<h1>{$thread->getSubject()}</h1>

{include file=thread_breadcrumb.html.tpl board=$board thread=$thread}

{assign var=boardid value=$board->getBoardID()}
{assign var=group value=$board->getGroup()}

<table>
{foreach from=$messages item=message}
<tr>
 <th>{$message->getSender()}<br />{$message->getDate()|date_format:"%d.%m.%Y %H:%M"}</th>
 <td>
  {foreach from=$message->getBodyParts() key=partid item=part}
  <div class="bodypart">
  {assign var=messageid value=$message->getMessageID()}
  {assign var=attachmentlink value=$datadir->getAttachmentWebPath($group,$part)}
  {if     $part->isInline() && $part->isText()}
   <pre>{$part->getText("UTF-8")}</pre>
  {elseif $part->isInline() && $part->isAttachment() && $part->isImage()}
   <img src="{$attachmentlink}" width="300px" />
  {elseif $part->isAttachment()}
   <a href="{$attachmentlink}">{$part->getFilename()}</a>
  {else}
   Attachment vorhanden, kann aber nicht angezeigt werden. Bitte melde diesen Fehler!
  {/if}
  </div>
  {/foreach}
 </td>
</tr>
{/foreach}
</table>
{include file=footer.html.tpl}
