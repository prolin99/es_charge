<{if $smarty.session.bootstrap==3}>
    <{includeq file="$xoops_rootpath/modules/$xoops_dirname/templates/b3/`$this_file`"}>
<{else}>
    <{includeq file="$xoops_rootpath/modules/$xoops_dirname/templates/b4/`$this_file`"}>
<{/if}>
