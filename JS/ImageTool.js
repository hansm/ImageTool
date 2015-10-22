/**
 * Image Tool
 *
 * @author Hans Mäesalu (hansmaesalu@gmail.com)
 * @license BSD Simplified https://opensource.org/licenses/BSD-3-Clause
 * @version 1
 */

/**
 * @param string fieldId ID of the DOM element that contains the form
 * @param string backendUrl backend URL where the image is posted to. Must be same domain due to XMLHttpRequest limitations
 * @param function|null uploadDone callback function to call when upload completes
 */
var ImageTool = function(fieldId, backendUrl, uploadDone) {
	this.elForm = $("#" + fieldId);
	this.elMessage = this.elForm.find(".message");
	this.elPreview = this.elForm.find(".preview");
	this.elActions = this.elForm.find(".actions");
	this.elFile = this.elForm.find(".file");
	this.elDone = this.elForm.find(".done");
	this.elResizeAction = this.elForm.find(".resizeAction");
	this.elCropAction = this.elForm.find(".cropAction");
	this.elUploading = this.elForm.find(".uploading");
	this.elCrop = null;

	this.attachEventListeners();
	this.showUploadForm();

	this.backendUrl = backendUrl;
	this.uploadDone = uploadDone;

	this.imageWidth = 0;
	this.imageHeight = 0;
	this.sizeRatio = 1;
	this.cropCor = {};
};

ImageTool.ALLOWED_TYPES = ["image/jpeg", "image/png"];

ImageTool.prototype = {

	getHeight: function() {
		return this.elForm.height();
	},

	getWidth: function() {
		return this.elForm.width();
	},

	attachEventListeners: function() {
		var $this = this;

		this.elFile.find("input").change(function(e) {
			e.preventDefault();

			if (this.files && this.files.length > 0) {
				$this.preview(this.files[0]);
			}
		});

		this.elActions.find("button").click(function(e) {
			e.preventDefault();
			switch (this.name) {
				case "cancel":
					$this.showUploadForm();
					break;
				case "upload":
					$this.upload();
					break;
				case "resize":
					$this.showResize();
					break;
				case "crop":
					$this.showCrop();
					break;
			}
		});

		// Resize
		this.elResizeAction.find("button").click(function(e) {
			e.preventDefault();
			if (this.name == "resize") {
				$this.resize();
			} else {
				$this.resizeCancel();
			}
		})

		// Crop
		this.elCropAction.find("button").click(function(e) {
			e.preventDefault();
			if (this.name == "crop") {
				$this.crop();
			} else {
				$this.cropCancel();
			}
		}).bind("mousedown mouseup", function(e) {
			e.stopPropagation();
			e.preventDefault();
		});
	},

	attachCropListeners: function() {
		var $this = this;
		this.elForm.mousedown(function(e) {
			e.preventDefault();
			var imagePos = $this.elPreview.offset();
			$this.startCrop(e.pageX - imagePos.left, e.pageY - imagePos.top);
		}).mouseup(function(e) {
			e.preventDefault();
			var imagePos = $this.elPreview.offset();
			$this.endCrop(e.pageX - imagePos.left, e.pageY - imagePos.top);
		});
	},

	detachCropListeners: function() {
		this.elForm
			.unbind("mousedown")
			.unbind("mouseup");
	},

	showUploadForm: function() {
		this.elMessage.hide();
		this.elPreview.hide();
		this.elActions.hide();
		this.elDone.hide();
		this.elResizeAction.hide();
		this.elUploading.hide();
		this.elCropAction.hide();
		this.elFile.show().children("input").val("");

		var topPos = (this.getHeight() - this.elFile.height()) / 2;
		var leftPos = (this.getWidth() - this.elFile.width()) / 2;
		this.elFile.css({top: topPos, left: leftPos});
	},

	preview: function(file) {
		var $this = this;
		this.elMessage.hide();

		if (!file.type || !inArray(file.type, ImageTool.ALLOWED_TYPES)) {
			this.errorMessage("Invalid file type.");
			return;
		}

		var reader = new FileReader();
		reader.onload = function (e) {
			var image = new Image();
			image.src = e.target.result;
			image.onload = function() {
				$this.cropCor = {};

				$this.imageWidth = image.width;
				$this.imageHeight = image.height;

				$this.elPreview.show().empty();
				$this.elPreview.append(image);

				$this.fitImage($this.imageWidth, $this.imageHeight);

				$this.showPreview();
			};
		};
		reader.readAsDataURL(file);
	},

	showPreview: function() {
		this.elActions.show();
		this.elFile.hide();
		this.elResizeAction.hide();
		this.elCropAction.hide();
	},

	fitImage: function(width, height) {
		var maxWidth = this.getWidth();
		var maxHeight = this.getHeight();
		this.sizeRatio = 1;

		if (width > maxWidth) {
			this.sizeRatio = width / maxWidth;
			width = maxWidth;
			height = Math.round(height / this.sizeRatio);
		}
		if (height > maxHeight) {
			this.sizeRatio = height / maxHeight;
			height = maxHeight;
			width = Math.round(width / this.sizeRatio);
		}

		this.elPreview.children("img")
			.width(width)
			.height(height);

		// Position
		var topPos = (maxHeight - height) / 2;
		var leftPos = (maxWidth - width) / 2;
		this.elPreview.css({
			width: width,
			height: height,
			top: topPos,
			left: leftPos
		});
	},

	showResize: function() {
		this.elActions.hide();
		this.elResizeAction.show();

		var elementWidth = this.elResizeAction.find("input[name='width']");
		if (!elementWidth.val()) {
			elementWidth.val(this.imageWidth);
		}

		var elementHeight = this.elResizeAction.find("input[name='height']");
		if (!elementHeight.val()) {
			elementHeight.val(this.imageHeight);
		}
	},

	resize: function() {
		var elementWidth = this.elResizeAction.find("input[name='width']");
		if (!elementWidth.val() || isNaN(elementWidth.val())) {
			elementWidth.addClass("invalid");
		} else {
			elementWidth.removeClass("invalid");
		}

		var elementHeight = this.elResizeAction.find("input[name='height']");
		if (!elementHeight.val() || isNaN(elementHeight.val())) {
			elementHeight.addClass("invalid");
		} else {
			elementHeight.removeClass("invalid");
		}

		this.fitImage(parseInt(elementWidth.val()), parseInt(elementHeight.val()));
		this.showPreview();
	},

	resizeCancel: function() {
		this.elResizeAction.find("input[name='width'],input[name='height']").val("");
		this.fitImage(this.imageWidth, this.imageHeight);
		this.showPreview();
	},

	showCrop: function() {
		this.elActions.hide();
		this.elCropAction.show();
		this.attachCropListeners();
	},

	crop: function() {
		this.detachCropListeners();
		this.showPreview();
	},

	cropCancel: function() {
		this.cropCor = {};

		if (this.elCrop && this.elCrop.size() > 0) {
			this.elCrop.remove();
			this.elCrop = null;
		}

		this.detachCropListeners();
		this.showPreview();
	},

	startCrop: function(x1, y1) {
		var $this = this;

		this.cropCor = {};
		this.cropCor.x1 = x1;
		this.cropCor.y1 = y1;

		this.elCropAction.hide();
		this.elCrop = this.elPreview.children(".crop");
		if (this.elCrop.size() == 0) {
			this.elCrop = $('<div class="crop"></div>')
							.appendTo(this.elPreview);
		}

		this.drawCrop(this.cropCor.x1, this.cropCor.y1,
			this.cropCor.x1 + 5, this.cropCor.y1 + 5);

		var imagePos = $this.elPreview.offset();
		this.elPreview.mousemove(function(e) {
			$this.drawCrop($this.cropCor.x1, $this.cropCor.y1,
				e.pageX - imagePos.left, e.pageY - imagePos.top);
		});
	},

	drawCrop: function(x1, y1, x2, y2) {
		if (x1 <= x2) {
			var left = x1;
			var width = x2 - x1;
		} else {
			var left = x2;
			var width = x1 - x2;
		}

		if (y1 <= y2) {
			var top = y1;
			var height = y2 - y1;
		} else {
			var top = y2;
			var height = y1 - y2;
		}

		this.elCrop.css({
			top: top,
			left: left,
			width: width,
			height: height
		});
	},

	endCrop: function(x2, y2) {
		this.elCropAction.show();
		this.elPreview.unbind("mousemove");
		this.cropCor.x2 = x2;
		this.cropCor.y2 = y2;

		this.drawCrop(this.cropCor.x1, this.cropCor.y1,
			this.cropCor.x2, this.cropCor.y2);
	},

	upload: function() {
		var $this = this;

		this.showUploading();
		var formData = new FormData(this.elForm.get(0));

		if (this.cropCor
				&& this.cropCor.x1
				&& this.cropCor.x2
				&& this.cropCor.y1
				&& this.cropCor.y2) {
			for (var i in this.cropCor) {
				formData.append(i, parseInt(this.cropCor[i] * this.sizeRatio));
			}
		}

		$.ajax({
			url: this.backendUrl,
			type: "POST",
			data: formData,
			processData: false,
			contentType: false
		})
		.success(function(response) {
			if (response.error) {
				$this.showUploadForm();
				$this.errorMessage(response.message || "Technical error.");
			} else {
				$this.showUploaded(response.message, response.url);
			}
		})
		.fail(function() {
			$this.showUploadForm();
			$this.errorMessage("Technical error.");
		});
	},

	showUploading: function() {
		this.elPreview.hide();
		this.elActions.hide();
		this.elUploading.show();

		var topPos = (this.getHeight() - this.elUploading.height()) / 2;
		this.elUploading.css("top", topPos);
	},

	showUploaded: function(message, url) {
		this.elUploading.hide();

		this.elDone.children(".doneMessage").text(message);
		this.elDone.children(".doneUrl")
			.html('<a href="' + url + '" target="_blank">' + url + '</a>');
		this.elDone.show();

		var topPos = (this.getHeight() - this.elDone.height()) / 2;
		this.elDone.css("top", topPos);

		if (this.uploadDone) {
			this.uploadDone(url);
		}
	},

	message: function(message) {
		this.elMessage
			.removeClass("error")
			.show()
			.html(message);
	},

	errorMessage: function(message) {
		this.elMessage
			.addClass("error")
			.show()
			.html(message);
	}

};

function inArray(needle, haystack) {
	for (var i in haystack) {
		if (haystack[i] == needle) {
			return true;
		}
	}
	return false;
}