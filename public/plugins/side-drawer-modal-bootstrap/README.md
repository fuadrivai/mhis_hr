# Bootstrap 4/5 Top Bottom, Side & Full Screen Modals

Bootstrap 4/5 Top Bottom, Side & Full Screen Modals

### FYI, Bootstrap v5 Beta3 comes with a new Offcanvas component - https://getbootstrap.com/docs/5.0/components/offcanvas/

## UPDATED 25/2/21 - Class names changed to .modal-*

<code>.modal-top</code> / <code>.modal-right</code> / <code>.modal-bottom</code> / <code>.modal-left</code>

Adheres to the available <code>.modal-dialog</code> widths - <code>.modal-sm</code> / <code>.modal-lg</code> / <code>.modal-xl</code>

https://codepen.io/xcartmods/pen/vYNdQpj

```
<button type="button" data-toggle="modal" data-target="#left_modal_sm">Left Side Modal SM</button>
```
Obviously for Bootstrap v5, you need to use <code>data-bs-toggle="modal"</code> and <code>data-bs-target="#left_modal_sm"</code>
```
<div class="modal modal-left fade" id="left_modal_sm" tabindex="-1" role="dialog" aria-labelledby="left_modal_sm">
  <div class="modal-dialog modal-sm" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Title</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        ...
      </div>
      <div class="modal-footer modal-footer-fixed">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary">Save changes</button>
      </div>
    </div>
  </div>
</div>
```
https://getbootstrap.com/docs/4.6/components/modal/

https://getbootstrap.com/docs/5.0/components/modal/
