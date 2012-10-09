<script type="text/javascript" src="{buildURL page='assets/syntaxhighlighter/scripts/shCore.js'}"></script>
<script type="text/javascript" src="{buildURL page='assets/syntaxhighlighter/scripts/shBrushJScript.js'}"></script>
<script type="text/javascript" src="{buildURL page='assets/syntaxhighlighter/scripts/shBrushYaml.js'}"></script>
<link href="{buildURL page='assets/syntaxhighlighter/styles/shCore.css'}" rel="stylesheet" type="text/css" />
<link href="{buildURL page='assets/syntaxhighlighter/styles/shThemeDefault.css'}" rel="stylesheet" type="text/css" />
{if $viewFailure}<div class="alert alert-error"><button type="button" class="close" data-dismiss="alert">X</button>{$viewFailure}</div>{/if}
{if $viewSuccess}<div class="alert alert-success"><button type="button" class="close" data-dismiss="alert">X</button>{$viewSuccess}</div>{/if}
<div width="100%">
    <div width="100%">
    <div class="span3 well well-large pull-right" style="margin:1em;display:block;">
        <h4 style="text-align:center;">{$dataToUse.name}</h2><br>
        <b>Author: </b><a href="{buildURL page='user/'}{$dataToUse.author}">{$dataToUse.author}</a><br>
        <b>Created: </b><abbr class="timeago" title="{$dateCreated}">{$dateCreated}</abbr><br>
        <b>Edited: </b><abbr class="timeago" title="{$dateEdited}">{$dateEdited}</abbr><br>
        <b>Views: </b>{$dataToUse.views}<br>
        <b>Downloads: </b>{$dataToUse.downloads}<br>
        <b>Likes: </b>{$likes}<br><br>
        <div style="margin:0 auto;text-align:center;"><button onclick='document.location.href="{buildURL page='raw/'}{$dataToUse.pubID}"' class="btn btn-info">View Raw</button> <button onclick='document.location.href="{buildURL page='download/'}{$dataToUse.pubID}"' class="btn btn-primary">Download</button><br><small><a href="{buildURL page='download/'}{$dataToUse.pubID}">WGET</a></small><br>
            <span style="margin:5px;margin-left:auto;margin-right:auto;">
                <button onclick='document.location.href="{buildURL page='action/1/'}{$dataToUse.pubID}"' rel="tooltip" title="Like" class="btn btn-success{if $liked} disabled{/if}"><i class="icon-thumbs-up"></i></button> 
                {if $ScriptRepo->loggedIn}{if $ScriptRepo->username==$dataToUse.author || $ScriptRepo->admin}<button onclick='document.location.href="{buildURL page='edit/'}{$dataToUse.pubID}"' rel="tooltip" title="Edit" class="btn btn-inverse"><i class="icon-edit icon-white"></i></button> {/if}{/if} 
                <button onclick='document.location.href="{buildURL page='post/'}{$dataToUse.pubID}"' rel="tooltip" title="Duplicate" class="btn"><i class="icon-share"></i></button> 
                {if $ScriptRepo->loggedIn}{if $ScriptRepo->admin}<button onclick='document.location.href="{buildURL page='action/4/'}{$dataToUse.pubID}"' rel="tooltip" title="Delete" class="btn btn-danger"><i class="icon-remove"></i></button>{/if}{/if} 
                <button onclick='document.location.href="{buildURL page='action/5/'}{$dataToUse.pubID}"' rel="tooltip" title="Report" class="btn btn-warning"><i class="icon-flag"></i></button> 
            </span>
        </div>
    </div>
    <div>
        <b>Description: </b>{$dataToUse.description|nl2br}<br><br>
    </div>
    </div>
    <div class="well" style="background-color:white;"><pre class="brush: {if $dataToUse.scriptType==1}js{else}yaml{/if}">{$code}</pre></div>
    <script type="text/javascript">
        SyntaxHighlighter.all()
    </script>
</div><br><br><br><br>
<div id="commentsZone">
    <legend>Comments</legend>
    <table class="table table-bordered">
        <tbody>
            {foreach $commentData as $comment}
                <tr>
                    <td><small>{$comment.timestamp}</small> - <a href="{buildURL page='user/'}{$comment.author}">{$comment.author}</a>: <button onclick='document.location.href="{buildURL page='action/6/'}{$comment.id}"' rel="tooltip" title="Report" class="btn btn-warning pull-right"><i class="icon-flag"></i></button>
                    <br>
                    <br>{$comment.content}</td>
                </tr>
            {foreachelse}
                <tr>
                    <td>No one has posted a comment! Post one below:</td>
                </tr>
            {/foreach}
        </tbody>
    </table>
</div>
<form id='post' method='post' accept-charset='UTF-8'>
    <fieldset>
        <textarea rows="4"{if !$ScriptRepo->loggedIn}disabled{/if} class="span7" id="commentField" name="commentField" placeholder="{if $ScriptRepo->loggedIn}Comment{else}Log in to comment!{/if}">{if $commentField}{$commentField}{/if}</textarea><br>
        <input class="btn btn-small btn-primary" type='Submit' name='Submit' value='Submit' /><br>
    </fieldset>
</form>