/*** DataTable stuff */


		$(document).ready(function(){
			var table = $('#myDataTable').DataTable( {
				"ajax": "/overview/getTableData",
				responsive: true,
				serverSide:true,
				processing:true,
				// add column that is not from the db
				"columnDefs": [ {
						"targets": -1,
						"data": null,
						"defaultContent": "<button>Stats</button>"
					},
					{
						"targets": 2,
						"visible": false,
						// this is needed for invisibility when responsive is true
						"className": "never", 
						"searchable": false
					}					
				 ],
				"columns": [
				    null,
				    null,
					null,
				    { "width": "5%", "orderable": false, "searchable": false }
				  ]
				}
			);
			$('#myDataTable tbody').on('click', 'button', function () {
				row = table.row( $(this).parents('tr') );
				var data = row.data();
				if ( data == null )
					alert("opgevouwen button; dat werkt niet");
				else
					alert( "run statistics for question "+ data[ 2 ] );
			} );
			/**
				$('#myDataTable tbody').on('click', 'tr', function () {
				var name = $('td', this).eq(2).text();
				var data = table.row( $(this).parents('tr') ).data();
				// use eq(1) instead of eq(0): username is always empty
				alert( 'You clicked on '+data[2]+'\'s row' );
			} );
			**/
		});
