@extends('admin::layouts.admin_template')
@section('content')
    <link href="https://unpkg.com/grapesjs/dist/css/grapes.min.css" rel="stylesheet" />
    <style type="text/css">
        /* Let's highlight canvas boundaries */
        #gjs {
            /*  border: 3px solid #444;*/
        }

        .gjs-one-bg {
            background-color: #eee;
        }

        .gjs-two-color {
            color: #232323;
        }

        /* Reset some default styling */
        .gjs-cv-canvas {
            top: 0;
            width: 85%;
            height: auto;
            margin-top: 40px;
            border: 1px solid #eee;
        }

        .gjs-pn-views-container {
            box-shadow: none;
        }

        .gjs__themed .gjs-one-bg {
            background-color: #78366a;
        }

        .gjs-pn-commands {
            top: 3px !important;
        }

        .gjs-block-label svg,
        .gjs-block__media svg {
            width: 54px;
        }
    </style>

    <p><a title="Main Module" href="{{ route('getManageCMS') }}"><i class="fa fa-chevron-circle-left "></i> &nbsp; Back To List
            Data Manage CMS</a></p>

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header card-primary align-items-center d-flex">
                    <h4 class="card-title mb-0 flex-grow-1">{{ $page_title }}</h4>
                    <div class="flex-shrink-0">
                    </div>
                </div>

                <div class="card-body">
                    <form action="{{ route('postUpdateCms', $row->id) }}" method="post" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="return_url" value="{{ route('getManageCMS') }}">
                        <div class="row">


                            <div class="col-md-6">
                                <div class="mb-3 ">
                                    <label class="form-label">Page Title <span class="text-danger"
                                            title="This field is required"></span></label>
                                    <input type="text" title="Page Title" class="form-control" name="page_title"
                                        value="{{ old('page_title') ? old('page_title') : $row->page_title }}"
                                        placeholder="Page Title" required readonly>
                                    @error('page_title')
                                        <div class="text-danger mt-1" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </div>
                                    @enderror
                                    <p class="text-muted"></p>
                                </div>
                            </div>



                            @if ($row->featured_image != '')
                                <div class="col-md-6">
                                    <!-- <div class="mb-3 ">
         <label class="form-label">Featured Image</label>
         <input type="file" class="form-control" name="image" accept="image/*">
         @error('image')
        <div class="text-danger mt-1" role="alert">
          <strong>{{ $message }}</strong>
          </div>
    @enderror
         <p class="text-muted">The image should be JPG/JPEG/PNG/GIF/SVG type and the image size should not above 5MB.</p>
         </div> -->
                                    <div class="form-group mb-3">
                                        <label class="label-setting">Featured Image</label>
                                        @if (
                                            !empty($row->featured_image) &&
                                                (Storage::exists($row->featured_image) || file_exists(public_path($row->featured_image))))
                                            <div class="prev-img-thumb"><img src="{{ asset($row->featured_image) }}"></div>
                                            <p class="text-muted"><em>* If you want to upload other image, please first
                                                    delete the image.</em></p>
                                            <p><a class="btn btn-danger btn-primary btn-sm"
                                                    href="{{ AdminHelper::adminpath() }}/download-file?image={{ $row->featured_image }}"><i
                                                        class="fa fa-download"></i> Download </a>
                                                <a class="btn btn-danger btn-delete btn-sm"
                                                    onclick="if(!confirm('Are you sure ?')) return false"
                                                    href="{{ AdminHelper::adminpath() }}/delete-image?image={{ $row->featured_image }}&&id={{ $row->id }}&&column=featured_image&table=cms_pages"><i
                                                        class="fa fa-ban"></i> Delete </a>
                                            </p>
                                        @else
                                            <input type="file" name="image" id="image" accept="image/*"
                                                class="form-control">
                                            <div class="text-muted">The image should be JPG/JPEG/PNG/GIF/SVG type and the
                                                image size should not above 2MB.</div>
                                            @error('image')
                                                <div class="text-danger mt-1" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </div>
                                            @enderror
                                        @endif
                                    </div>
                                </div>
                            @endif

                            <div class="col-md-12">
                                <div class="mb-3 ">
                                    <label class="form-label">Page Content <span class="text-danger"
                                            title="This field is required">*</span></label>
                                    <input id="project-html" type="hidden" name="content"
                                        value="{{ old('page_content') ? old('content') : $row->page_content }}" />
                                    <input id="project-data" type="hidden" name="html_data"
                                        value="{{ old('html_data') ? old('html_data') : $row->html_data }}" />
                                    <div id="gjs">

                                    </div>
                                    <div id="blocks"></div>
                                    @error('page_content')
                                        <div class="text-danger mt-1" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </div>
                                    @enderror
                                    <p class="text-muted"></p>
                                </div>
                            </div>



                            <div class="col-md-6">
                                <div class="mb-3 ">
                                    <label class="form-label">Meta Title <span class="text-danger"
                                            title="This field is required">*</span></label>
                                    <input type="text" title="Meta Title" class="form-control" name="meta_title"
                                        value="{{ old('meta_title') ? old('meta_title') : $row->meta_title }}"
                                        placeholder="Meta Title" required>
                                    @error('meta_title')
                                        <div class="text-danger mt-1" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </div>
                                    @enderror
                                    <p class="text-muted"></p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3 ">
                                    <label class="form-label">Meta Keywords</label>
                                    <input type="text" title="Meta Keywords" class="form-control" name="meta_keywords"
                                        value="{{ old('meta_keywords') ? old('meta_keywords') : $row->meta_keywords }}">
                                    @error('meta_keywords')
                                        <div class="text-danger mt-1" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </div>
                                    @enderror
                                    <p class="text-muted"></p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3 ">
                                    <label class="form-label">Meta Description</label>
                                    <textarea name="meta_description" class="form-control" rows="3">{{ old('meta_description') ? old('meta_description') : $row->meta_description }}</textarea>
                                    @error('meta_description')
                                        <div class="text-danger mt-1" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </div>
                                    @enderror
                                    <p class="text-muted"></p>
                                </div>
                            </div>

                            <input type="hidden" name="status" value="1">

                        </div>
                        <div class="row g-3">
                            <div class="form-group">
                                <label class="control-label col-sm-2"></label>
                                <div class="col-sm-10">
                                    <a href="{{ route('getManageCMS') }}" class="btn btn-default"><i
                                            class="fa fa-chevron-circle-left"></i> Back</a>
                                    <input type="submit" name="submit" value="Save" class="btn btn-primary">
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('bottom')
    <script src="{{ asset('assets/plugins/grapesjs/recipe_theme.js') }}"></script>
    <script src="https://unpkg.com/grapesjs"></script>
    <script src="https://unpkg.com/grapesjs-blocks-basic"></script>
    <!-- <script src="https://unpkg.com/grapesjs-plugin-forms"></script> -->
    <script src="https://unpkg.com/grapesjs-custom-code"></script>
    <script src="https://unpkg.com/grapesjs-plugin-ckeditor"></script>
    <script src="https://unpkg.com/grapesjs-preset-webpage"></script>
    <script src="https://unpkg.com/@silexlabs/grapesjs-fonts"></script>
    <script type="text/javascript">
        $(document).ready(function() {
            /*$('#description').summernote({
            	height: 300,
            	placeholder: 'Type here...'
            });*/

            // CKEDITOR.replace( 'description',{
            // 	allowedContent : true,
            // 	versionCheck: false
            // });

            /*ClassicEditor
    		.create( document.querySelector( '#description' ) )
    	.catch( error => {
    		console.error( error );
    	} );*/

            // Inline storage
            const inlineStorage = (editor) => {
                const projectDataEl = document.getElementById('project-data');
                const projectHtmlEl = document.getElementById('project-html');

                editor.Storage.add('inline', {
                    load() {
                        return JSON.parse(projectDataEl.value || '{}');
                    },
                    store(data) {
                        const component = editor.Pages.getSelected().getMainComponent();
                        projectDataEl.value = JSON.stringify(data);
                        projectHtmlEl.value = `${editor.getHtml({ component })}`;
                    }
                });
            };

            const editor = grapesjs.init({
                // Indicate where to init the editor. You can also pass an HTMLElement
                container: '#gjs',
                fromElement: true,
                height: '600px',
                width: 'auto',
                canvas: {
                    styles: ["{{ asset('assets/css/style.css') }}",
                        "{{ asset('assets/css/vendor.min.css') }}",
                        "{{ asset('assets/css/media.css') }}"
                    ]
                },
                blockManager: {
                    blocks: [{
                            id: 'section', // id is mandatory
                            media: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M19 3H5c-1.11 0-2 .89-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5a2 2 0 0 0-2-2m0 2v14H5V5h14z"></path></svg>',
                            label: 'Abschnitt',
                            attributes: {
                                class: 'gjs-block-section'
                            },
                            content: `<section>
	          <h1>This is a simple title</h1>
	          <div>This is just a Lorem text: Lorem ipsum dolor sit amet</div>
	        </section>`,
                        },
                        {
                            id: 'headings', // id is mandatory
                            media: '<img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAADIAAAAyCAYAAAAeP4ixAAAACXBIWXMAAAsTAAALEwEAmpwYAAAAjElEQVR4nO2TSQ7AMAgD+f+zuryL3jm0kUViRZ2RuIbgkSMALNwRkS9z7bIrB6aLqbs4RAAj9pQwIoARAcpuTwkjAhgRoOz2lDAyycjKkXF/PDmk4DaQGCm4DSRGCr8y0gWH2FPCiABGBCi7PSWMCGBEgLLbU8KIAEYEKLs9pZW7zo+Hj747lu4CiEEecniiqq1YrPIAAAAASUVORK5CYII=">',
                            label: 'Überschriften',
                            attributes: {
                                class: 'gjs-block-headings'
                            },
                            content: `<h1>H1</h1><h2>H2</h2><h3>H3</h3><h4>H4</h4><h5>H5</h5><h6>H6</h6>`,
                        }
                    ]
                },
                //plugins: [inlineStorage, "gjs-blocks-basic","grapesjs-custom-code","grapesjs-plugin-ckeditor"],
                plugins: [inlineStorage, "grapesjs-preset-webpage", "gjs-blocks-basic",
                    "grapesjs-custom-code", "grapesjs-plugin-ckeditor"
                ],
                pluginsOpts: {
                    'grapesjs-preset-webpage': {
                        blocks: []
                    },
                    'gjs-blocks-basic': {
                        blocks: ['column1', 'column2', 'column3', 'column3-7', 'text', 'link', 'image'],
                        labelColumn1: '1 Split',
                        labelColumn2: '2 Split',
                        labelColumn3: '3 Split',
                        labelColumn37: '2 Split 3/7',
                        labelText: 'Text',
                        labelLink: 'Link',
                        labelImage: 'Picture',
                    },
                    'grapesjs-custom-code': {
                        blockCustomCode: {
                            label: 'Custom code',
                            category: 'Basic'
                        },
                        modalTitle: 'Enter your code',
                        buttonLabel: 'Save',
                    },
                    'grapesjs-plugin-ckeditor': {
                        options: {
                            language: 'en',
                            versionCheck: false
                        },
                    },
                },
                storageManager: {
                    type: 'inline'
                },
            });

            // editor.on("load", () => {
            //     editor.Panels.addPanel({
            //       id: "basic-actions",
            //       el: ".panel__basic-actions",
            //       buttons: [
            //         {
            //           id: "create-button",
            //           label: "Open font dialog",
            //           command(editor) {
            //             editor.runCommand("open-fonts");
            //           }
            //         }
            //       ]
            //     });
            //   });
            // const styleManager = editor.StyleManager
            // const fontProperty = styleManager.getProperty('typography', 'font-family')
            // fontProperty.setOptions([{id: 'Mulish, sans-serif', label: 'Mulish'}])
            // styleManager.render()

        });
    </script>
@endpush
