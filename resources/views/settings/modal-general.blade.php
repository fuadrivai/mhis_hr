<div class="modal fade" id="modal-data" tabindex="-1" role="dialog" aria-modal="true" aria-labelledby="modal-dataLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="form-data"  autocomplete="OFF" method="POST">
                @csrf
                <div id="form-method">
                </div>
                <div class="modal-header">
                    <h5 class="modal-title" id="modal-dataLabel">Form</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close" >
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div id="form-password" class="form-group">
                        <label for="name">Name</label>
                        <input type="text" id="id" class="form-control d-none" name="id" />
                        <input type="text" id="name" class="form-control" name="name" required/>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Submit</button>
                </div>
            </form>
        </div>
    </div>
</div>