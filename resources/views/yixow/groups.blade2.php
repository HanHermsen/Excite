


<div data-role="page" id='pageOne'>
	@include('yixow.header')
	</div>
	<div role="main" class="ui-content">
	<br />
		<p>Hier komen de Groepen</p>
	</div><!-- /content -->

	@include('yixow.menuPanel')

	<div data-role="footer" data-position='fixed' style='background-color: transparent; border-color: transparent;'>
	<a href="#" data-role="button" data-icon="plus" data-iconpos="notext"  style='float: right' >Menu</a>
		<!-- <h4>Page Footer</h4> -->
	</div><!-- /footer -->
	<script>
		Excite.y.pageId = Excite.y.GROUPS;
		Excite.y.highlight(Excite.y.GROUPS);
	</script>
</div><!-- /page -->
