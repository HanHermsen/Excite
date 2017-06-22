<script>
//console.log("hallo hier");
</script>

			<!-- panel content goes here; must be before the footer -->
	<!-- <div data-role="panel" data-position="left" data-position-fixed="false" data-display="overlay" id="nav-panel" data-theme="a"> -->
		<div data-role="panel" data-position="left" id="nav-panel" data-display="overlay" data-position-fixed="true" data-theme="a" style='height: 300px;'>

			<ul data-role="listview" data-theme="a" data-divider-theme="a" style="margin-top:-16px;" class="nav-search">
				<!--<li data-icon="delete" style="background-color:#111;">
					<a href="#" data-rel="close">Close menu</a>
				</li>-->
				<li><img src='/images/yixowklein.png'></li>
				<li>
					<a href="/yixow/" id='choice0'><span class='menuSelect' onclick='Excite.y.menuChoice($(this),Excite.y.QUESTIONS)'>Vragen</span></a>
				</li>
				<li>
					<a href="/yixow/groups" id='choice1'><span onclick='Excite.y.menuChoice($(this),Excite.y.GROUPS)'>Groepen</span></a>
				</li>
				<li>
					<a href="/yixow/groupsOffered" id='choice2'><span onclick='Excite.y.menuChoice($(this),Excite.y.GROUPS_OFFERED)'>Groepen aanbod</span></a>
				</li>
				<li data-filtertext="anatomy of page viewport">
					<a href="#">Profiel</a>
				</li>
				<li data-filtertext="events api animationComplete transition css">
					<a href="#">Mijn vragen</a>
				</li>
				<li data-filtertext="listview autodivider">
					<a href="#">Mijn antwoorden</a>
				</li>
				<hr />
				<li data-filtertext="button link submit cancel image reset mini buttonmarkup enable disable">
					<a href="#">Instellingen</a>
				</li>
				<li data-filtertext="button icon">
					<a href="#">Yixow delen</a>
				</li>
			</ul>
		</div><!-- /panel -->
