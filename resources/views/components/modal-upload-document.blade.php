<div>
    <div class="modal fade" id="{{ $id }}" tabindex="-1" role="dialog" aria-labelledby="documentUploadLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header border-bottom">
                    <h5 class="modal-title" id="documentUploadLabel">
                        <i class="fa fa-upload mr-2"></i>Upload Document
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="documentForm">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="docCategorySelect">Document Category</label>
                                    <select id="docCategorySelect" class="form-control select2" style="width: 100%">
                                        @foreach ($categories as $item)
                                            <option value="{{ $item['id'] }}">{{ $item['name'] }}</option>
                                        @endforeach
                                        <option value="custom">Custom</option>
                                    </select>
                                    <input type="text" id="docCategoryCustom" class="form-control mt-2"
                                        placeholder="Enter custom document name" style="display: none;">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="documentNumber">Document Number</label>
                                    <input type="text" class="form-control" id="documentNumber"
                                        placeholder="Enter document number">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="issuedDate">Issued Date</label>
                                    <input type="text" class="form-control date-picker" id="issuedDate"
                                        placeholder="Select issued date">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="expiryDate">Expiry Date <small id="expiryDateLabel">(if
                                            applicable)</small></label>
                                    <input type="text" class="form-control date-picker" id="expiryDate"
                                        placeholder="Select expiry date">
                                </div>
                            </div>
                        </div>
                        <!-- File Upload Area -->
                        <div class="form-group">
                            <label><strong>Upload File</strong></label>
                            <input type="file" id="documentFile" class="form-control"
                                accept=".pdf,.jpg,.jpeg,.png,.doc,.docx" required>
                        </div>
                        <!-- preview area -->
                        <div id="fileInfo" class="mt-2" style="display:none;">
                            <div id="filePreview"></div>
                            <small id="fileName" class="text-muted"></small>
                        </div>
                        <div class="form-group">
                            <label for="docNotes">Notes <small>(optional)</small></label>
                            <textarea class="form-control" id="docNotes" rows="3" placeholder="Add any additional notes..."></textarea>
                        </div>

                    </div>
                    <div class="modal-footer border-top">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">
                            <i class="fa fa-times mr-2"></i>Cancel
                        </button>
                        <button type="submit" class="btn btn-primary" id="submitDocumentBtn">
                            <i class="fa fa-upload mr-2"></i>Upload Document
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
