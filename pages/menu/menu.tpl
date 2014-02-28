<div class="menu">
{#foreach $P.parents as p}
<a href="#{$T.p}"><strong>{$T.p$key}</strong></h2></a>
{#/for}
{#if $T.dirs.length}
<ul>
	{#foreach $T.dirs.obj as p}
		<li>
			<a href="#{$P.state}.{$P.nstate}/{$T.p$key}">{$T.p$key}</a>
		</li>
	{#/for}
</ul>
{#/if}
{#if $T.files.length}
<ul style="margin-top:5px">
	{#foreach $T.files as p}
		{#if !$T.dirs.obj[$T.p.name]}
			<li>
				<a href="#{$P.state}.{$P.nstate}/{$T.p.name}">{$T.p.name}</a>
			</li>
		{#/if}
	{#/for}
</ul>
{#/if}
</div>