//Modal
<button type="button" data-modal="#exampleModal" aria-haspopup="dialog" aria-controls="exampleModal">Modal</button>
<div id="exampleModal" class="modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" aria-modal="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content bg-white border border-gray-200 max-w-2xl rounded-2xl">
			<div class="modal-header">
				<h3 id="exampleModalLabel" class="modal-title">عنوان مودال</h3>
				<button type="button" class="btn-close" aria-label="بستن مودال"></button>
			</div>
			<div class="modal-body">
			</div>
		</div>
	</div>
</div>


//Offcanvas start/end/bottom/top
<button type="button" data-offcanvas="#offcanvasStart" aria-haspopup="dialog" aria-controls="offcanvasStart">از راست</button>
<div id="offcanvasStart" class="offcanvas offcanvas-start" tabindex="-1" role="dialog" aria-labelledby="offcanvasStartLabel" aria-hidden="true" aria-modal="true">
	<div class="offcanvas-header">
		<h3 id="offcanvasStartLabel" class="offcanvas-title">منوی کناری</h3>
		<button 
			type="button" 
			class="btn-close" 
			aria-label="بستن منو">
		</button>
	</div>
	<div class="offcanvas-body">
		lorem
	</div>
</div>

//Drawer
<button type="button" data-drawer="#simpleDrawer" aria-haspopup="dialog" aria-controls="simpleDrawer">باز کردن کشو ساده</button>
<div id="simpleDrawer" class="drawer" tabindex="-1" role="dialog" aria-labelledby="simpleDrawerLabel" aria-hidden="true" aria-modal="true">
	<div class="drawer-handle" aria-label="دستگیره کشو" role="separator"></div>
	<div class="drawer-header">
		<h3 id="simpleDrawerLabel" class="drawer-title">محتوای طولانی</h3>
	</div>
	<div class="drawer-body">
		Lorem ipsum dolor sit amet consectetur, adipisicing elit. Tenetur, accusantium id optio voluptates veritatis aliquid quibusdam ut. Esse facilis nostrum laborum inventore accusamus ipsa odio sed voluptatibus voluptatum, voluptate ducimus!
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