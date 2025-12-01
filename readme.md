<buttn data-pjmodal="#exampleModal">Open</buttn>


<div id="exampleModal" class="modal" tabindex="-1" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content bg-white border border-gray-200 max-w-2xl rounded-2xl">
			<div class="moda-header">
				<button type="button" class="btn-close">Close</button>
			</div>
			<div class="modal-body">
				lorem
			</div>
			<div class="moda-header">
				TEST
			</div>
		</div>
	</div>
</div>

//Collapse
<button class="btn-collapse" aria-expanded="false" aria-controls="collapse-content" id="collapse-button">Section 1</button>
<div id="collapse-content" role="region" aria-labelledby="collapse-button" class="hidden">
	<p>Content for section 1</p>
</div>

//TABLIST
<div class="tablist">
	<div role="tablist">
		<button type="button" class="btn-collapse" id="unstyled-tabs-item-1" aria-selected="true" aria-controls="unstyled-tabs-1" role="tab">Tab 1</button>
		<button type="button" class="btn-collapse" id="unstyled-tabs-item-2" aria-selected="false" aria-controls="unstyled-tabs-2" role="tab">Tab 2</button>
		<button type="button" class="btn-collapse" id="unstyled-tabs-item-3" aria-selected="false" aria-controls="unstyled-tabs-3" role="tab">Tab 3</button>
	</div>
	<div id="unstyled-tabs-1" role="tabpanel" aria-labelledby="unstyled-tabs-item-1">
		This is the <em>first</em> item's tab body.
	</div>
	<div id="unstyled-tabs-2" class="hidden" role="tabpanel" aria-labelledby="unstyled-tabs-item-2">
		This is the <em>second</em> item's tab body.
	</div>
	<div id="unstyled-tabs-3" class="hidden" role="tabpanel" aria-labelledby="unstyled-tabs-item-3">
		This is the <em>third</em> item's tab body.
	</div>
</div>

//Accardion
<div class="accordion">
	<div class="accordion-item">
		<h3>
			<button class="btn-collapse" aria-expanded="false" aria-controls="section1" id="accordion1id">Section 1</button>
		</h3>
		<div id="section1" role="region" aria-labelledby="accordion1id" class="hidden">
			<p>Content for section 1</p>
		</div>
	</div>
	<div class="accordion-item">
		<h3>
		<button class="btn-collapse" aria-expanded="false" id="accordion2id" aria-controls="section2">Section 2</button>
		</h3>
		<div id="section2" role="region" aria-labelledby="accordion2id" class="hidden">
			<p>Content for section 2</p>
		</div>
	</div>
</div>