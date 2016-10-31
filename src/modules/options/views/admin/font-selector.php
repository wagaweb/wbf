<script type="text/template" id="font-select-tpl">
	<style type="text/css">
		<% _.each(fonts,function(el){ %>
		<% if(el.family == selected_font){ %>
		@import 'https://fonts.googleapis.com/css?family=<%= el.family %>:<%= el.variants.toString()%>';
			<% } %>
		<% }) %>
	</style>
	<div class="font-select-wrapper">
		<button data-remove-font class="button-secondary remove-font-button">X</button>
		<select data-fontlist class="all-fonts-select" name="<%= optionName %>[import][<%= counter %>][family]">
			<option value="" <% if ("" == selected_font) { %>selected="selected"<% } %>></option>
			<% _.each(fonts,function(el){ %>
			<% if(el.family == selected_font){ %>
			<option selected="selected" value="<%= el.family %>"><%= el.family %></option>
			<% } else { %>
			<option value="<%= el.family %>"><%= el.family %></option>
			<% } %>
			<% }) %>
		</select>

		<% _.each(fonts,function(el){ %>
		<% if(el.family == selected_font){ %>
        <div class="font-subset-wrap">
		<% _.each(el.subsets, function(subset){ %>
		<% if( typeof selected_charset !== "undefined" && selected_charset.indexOf(subset) != -1){ %>
		<input type="checkbox" checked="checked" class="font-subset-checkbox" name="<%= optionName %>[import][<%= counter %>][subset][]" value="<%= subset %>"/> <%= subset %>
		<% } else { %>
		<input type="checkbox" class="font-subset-checkbox" name="<%= optionName %>[import][<%= counter %>][subset][]" value="<%= subset %>"/> <%= subset %>
		<% } %>
		<% }) %>
        </div>
		<% } %>
		<% }) %>
		<div class="font-weight-selector" data-font-weight-selector>
			<% _.each(fonts,function(el){ %>
			<% if( el.family == selected_font) { %>
			<% _.each(el.variants, function(weight){ %>
			<span class="font-weight-selector-preview">
							<% if (weight.match(/italic/i)){ %>
                                <% replaced_weight = weight.replace(/italic/i, ''); %>
								<p style="font-family: '<%= el.family %>'; font-weight:<%= replaced_weight %>; font-style: italic;">consectetur adipisci elit, sed eiusmod tempor incidunt ut labore</p>

                            <%} else { %>
								<p style="font-family: '<%= el.family %>'; font-weight:<%= weight %>;">consectetur adipisci elit, sed eiusmod tempor incidunt ut labore</p>
							<% } %>
							<% if( typeof selected_weight !== "undefined" && selected_weight.indexOf(weight) != -1){ %>
								<input type="checkbox" checked="checked" class="font-weight-checkbox" name="<%= optionName %>[import][<%= counter %>][weight][]" value="<%= weight %>"/> <%= weight %>
							<% } else { %>
								<input type="checkbox" class="font-weight-checkbox" name="<%= optionName %>[import][<%= counter %>][weight][]" value="<%= weight %>"/> <%= weight %>
							<% } %>
						</span>
			<% }) %>
			<% } %>
			<% }) %>
		</div>
	</div>
</script>
<!--
// per qualche motivo questo template non funziona dentro font-selector-container.js

<script type="text/template" id="font-assign-container-tpl">
	<div class='section' data-font-assign>
		<h4>Title: </h4><span data-font-title></span>

		<h4 data-font-paragraph>Paragrapph: </h4>

	</div>
	<div class='section' data-font-try>
		<h1>Lorem Ipsum dolor</h1>
		<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vivamus nec sollicitudin tellus. Suspendisse auctor dui vel lorem efficitur pulvinar. Nullam sollicitudin justo gravida, blandit arcu in, fermentum urna. Integer commodo, mi eu scelerisque sollicitudin, libero purus pharetra est, ut cursus ipsum lacus sed leo. Phasellus maximus non diam nec rutrum. Fusce ac luctus dolor. Vivamus tincidunt felis id eros pharetra, eu feugiat diam egestas. Ut vel aliquet erat. Phasellus et nisl risus.
		</p>
	</div>
</script>

-->
<script type="text/template" id="font-assign-inner-tpl">
	<h4><%= cssSelector %>: </h4>
	<select data-font-assigned-list name="<%= optionName %>[assign][<%= cssSelector %>][family]" class="font-assigned-list">
		<option value="" <% if ("" == selectedFont) { %>selected="selected"<% } %>></option>
		<% _.each(fonts,function(el){ %>
		<% if (el == selectedFont) { %>
		<option selected="selected" value="<%= el %>"><%= el %></option>
		<% } else { %>
		<option value="<%= el %>"><%= el %></option>
		<% } %>
		<% }) %>
	</select>
	<span data-weight-assigned-list>
        <% _.each(fontWeights[selectedFont],function(weight){ %>
			<% if (weight == selectedWeight) { %>
				<input checked="checked" name="<%= optionName %>[assign][<%= cssSelector %>][weight]" type="radio" class="font-weight-assigner" value="<%= weight %>"><%= weight %>
			<% } else { %>
				<input name="<%= optionName %>[assign][<%= cssSelector %>][weight]" type="radio" class="font-weight-assigner" value="<%= weight %>"><%= weight %>
			<% } %>
		<% }) %>
    </span>
</script>