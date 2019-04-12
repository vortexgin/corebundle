var BootstrapCustom = {
    nav: {
        tab: {
            create: function(id, label, eOpts) {
                var html = '',
                    opts = {
                        index: 0,
                        selected: '',
                        tabCss: 'mr-5',
                        css: 'nav-link bg-transparent text-size-nm text-decoration-none'
                    };
                if (!empty(eOpts)) {
                    $.extend(opts, eOpts);
                }

                html += '<li class="nav-item ' + opts.tabCss + '">';
                html += '<a class="' + opts.css + ' ' + opts.selected + '" id="' + id + '-tab" data-toggle="tab" href="#' + id + '" role="tab" aria-controls="' + id + '" aria-selected="true">' + label + '</a>';
                html += '</li>';

                return html;
            }
        },
        tabContent: {
            create: function(id, content, eOpts) {
                var html = '',
                    opts = {
                        index: 0,
                        selected: '',
                        css: 'content bg-white p-5'
                    };
                if (!empty(eOpts)) {
                    $.extend(opts, eOpts);
                }

                html += '<div class="tab-pane fade ' + opts.css + ' ' + opts.selected + '" id="' + id + '" role="tabpanel" aria-labelledby="' + id + '-tab">';
                html += content;
                html += '</div>';

                return html;
            }
        }
    },
    form: {
        text: {
            create: function(label, name, eOpts) {
                var html = '',
                    opts = {
                        value: '',
                        required: '',
                        placeholder: '',
                        labelCss: 'text-0069a2 text-size-xs',
                        css: 'p-4'
                    };
                if (label != false) {
                    html += '<label class="' + opts.labelCss + ' ' + opts.required + '" for="' + name.slugify() + '">' + label + '</label>';
                    opts.placeholder = label;
                }
                if (!empty(eOpts)) {
                    $.extend(opts, eOpts);
                }
                html += '<input type="text" id="' + name.slugify() + '" name="' + name + '" value="' + opts.value + '" ' + opts.required + ' placeholder="' + opts.placeholder + '" class="form-control ' + opts.css + '" />';

                return html;
            }
        },
        email: {
            create: function(label, name, eOpts) {
                var html = '',
                    opts = {
                        value: '',
                        required: '',
                        placeholder: '',
                        labelCss: 'text-0069a2 text-size-xs',
                        css: 'p-4'
                    };
                if (label != false) {
                    html += '<label class="' + opts.labelCss + ' ' + opts.required + '" for="' + name.slugify() + '">' + label + '</label>';
                    opts.placeholder = label;
                }
                if (!empty(eOpts)) {
                    $.extend(opts, eOpts);
                }
                html += '<input type="email" id="' + name.slugify() + '" name="' + name + '" value="' + opts.value + '" ' + opts.required + ' placeholder="' + opts.placeholder + '" class="form-control ' + opts.css + '" />';

                return html;
            }
        },
        textarea: {
            create: function(label, name, eOpts) {
                var html = '',
                    opts = {
                        value: '',
                        required: '',
                        placeholder: '',
                        labelCss: 'text-0069a2 text-size-xs',
                        css: 'p-4'
                    };
                if (label != false) {
                    html += '<label class="' + opts.labelCss + ' ' + opts.required + '" for="' + name.slugify() + '">' + label + '</label>';
                    opts.placeholder = label;
                }
                if (!empty(eOpts)) {
                    $.extend(opts, eOpts);
                }
                html += '<textarea id="' + name.slugify() + '" name="' + name + '" ' + opts.required + ' placeholder="' + opts.placeholder + '" class="form-control ' + opts.css + '">' + opts.value + '</textarea>';

                return html;
            }
        },
        select: {
            create: function(label, name, options, eOpts) {
                var html = '',
                    opts = {
                        value: '',
                        required: '',
                        labelCss: 'text-0069a2 text-size-xs',
                        css: 'p-4'
                    };
                if (!empty(eOpts)) {
                    $.extend(opts, eOpts);
                }
                if (label != false) {
                    html += '<label class="' + opts.labelCss + ' ' + opts.required + '" for="' + name.slugify() + '">' + label + '</label>';
                }

                html += '<select id="' + name.slugify() + '" name="' + name + '" class="form-control ' + css + '">';
                if (!empty(options)) {
                    options.forEach(function(opt, index) {
                        var selected = '';
                        if (!empty(opts.value)) {
                            if (opt == opts.value) {
                                selected = 'selected="selected"';
                            }
                        }
                        html += '<option value="' + opt + '" ' + selected + '>' + opt + '</option>';
                    });
                }
                html += '</select>';

                return html;
            }
        },
        radio: {
            create: function(label, name, options, eOpts) {
                var html = '',
                    opts = {
                        value: '',
                        required: '',
                        labelCss: 'text-0069a2 text-size-xs',
                        css: 'p-4'
                    };
                if (!empty(eOpts)) {
                    $.extend(opts, eOpts);
                }
                if (label != false) {
                    html += '<label class="' + opts.labelCss + ' ' + opts.required + '" for="' + name.slugify() + '">' + label + '</label>';
                }

                if (!empty(options)) {
                    options.forEach(function(opt, index) {
                        var checked = '',
                            no = index + 1;
                        if (!empty(opts.value)) {
                            if (opt == opts.value) {
                                checked = 'checked="checked"';
                            }
                        }

                        html += '<div class="form-check">';
                        html += '<input type="radio" id="' + name.slugify() + '_' + index + '" name="' + name + '" class="form-check-input" value="' + opt + '" ' + checked + '>';
                        html += '<label class="form-check-label text-0069a2 text-size-xs pl-2" for="' + name.slugify() + '_' + index + '">' + opt + '</label>';
                        html += '</div>';

                        if (no % 2 == 0) {
                            html += '<div class="clearfix"></div>';
                        }
                    });
                }

                return html;
            }
        },
        checkbox: {
            create: function(label, name, options, eOpts) {
                var html = '',
                    opts = {
                        value: '',
                        required: '',
                        labelCss: 'text-0069a2 text-size-xs',
                        css: 'p-4'
                    };
                if (!empty(eOpts)) {
                    $.extend(opts, eOpts);
                }
                if (label != false) {
                    html += '<label class="' + opts.labelCss + ' ' + opts.required + '" for="' + name.slugify() + '">' + label + '</label>';
                }

                if (!empty(options)) {
                    options.forEach(function(opt, index) {
                        var checked = '',
                            no = index + 1;
                        if (!empty(opts.value)) {
                            if (opt == opts.value) {
                                checked = 'checked="checked"';
                            }
                        }

                        html += '<div class="form-check">';
                        html += '<input type="checkbox" id="' + name.slugify() + '_' + index + '" name="' + name + '" class="form-check-input" value="' + opt + '" ' + checked + '>';
                        html += '<label class="form-check-label text-0069a2 text-size-xs pl-2" for="' + name.slugify() + '_' + index + '">' + opt + '</label>';
                        html += '</div>';

                        if (no % 2 == 0) {
                            html += '<div class="clearfix"></div>';
                        }
                    });
                }

                return html;
            }
        }
    }
};