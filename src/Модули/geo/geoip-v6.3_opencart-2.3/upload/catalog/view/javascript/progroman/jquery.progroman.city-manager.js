if (window.Prmn === undefined) {
    Prmn = {};
}

Prmn.CityManager = function(options) {
    this.switchers = [];
    this.messages = [];
    this.options = $.extend({}, Prmn.CityManager.DEFAULTS, options);
    this.url_module = this.getHttpHost() + 'index.php?route=extension/module/progroman/city_manager';
    this.cities_popup = null;
    this.confirm_shown = false;
};

Prmn.CityManager.DEFAULTS = {
    base_path: 'auto'
};

Prmn.CityManager.prototype.addSwitcher = function(el) {
    this.switchers.push(new Prmn.CitySwitcher(el));
};

Prmn.CityManager.prototype.addMessage = function(el) {
    this.messages.push(new Prmn.CityMessage(el));
};

Prmn.CityManager.prototype.loadData = function() {
    var self = this, i;
    var need_ajax = this.messages.length > 0;

    if (!need_ajax) {
        for (i in this.switchers) {
            if (!this.switchers[i].loaded) {
                need_ajax = true;
            }
        }
    }
    if (need_ajax) {
        $.get(
            this.url_module + '/init',
            {url: location.pathname + location.search},
            function(json) {
                var i, j;
                for (i in self.switchers) if (self.switchers.hasOwnProperty(i)) {
                    if (!self.switchers[i].loaded) {
                        self.switchers[i].setContent(json.content);
                    }
                }

                if (json.messages) {
                    for (i in json.messages) {
                        for (j in self.messages) if (self.messages.hasOwnProperty(j)) {
                            if (self.messages[j].key === i) {
                                self.messages[j].setContent(json.messages[i]);
                            }
                        }
                    }
                }

                for (i in self.messages) if (self.messages.hasOwnProperty(i)) {
                    self.messages[i].setDefault();
                }
            },
            'json');
    }
};

Prmn.CityManager.prototype.showCitiesPopup = function() {
    var self = this;
    if (!this.cities_popup) {
        $.get(this.url_module + '/cities', function(html) {
            self.cities_popup = $(html);
            $('body').append(self.cities_popup);
            self.cities_popup.find('.prmn-cmngr-cities__city-name').click(function() {
                self.setFias($(this).data('id'));
                self.cities_popup.modal('hide');
            });

            self.hideAllConfirm();
            self.cities_popup.modal('show');

            var input = self.cities_popup.find('.prmn-cmngr-cities__search');
            self.autocomplete(input);
            input.focus();
        }, 'html');
    } else {
        self.hideAllConfirm();
        self.cities_popup.modal('show');
    }
};

Prmn.CityManager.prototype.hideAllConfirm = function() {
    var i;
    for (i in this.switchers) {
        this.switchers[i].hideConfirm();
    }
};

Prmn.CityManager.prototype.autocomplete = function(el) {
    var xhr = false;
    var self = this;
    el.prmn_autocomplete({
        'source': function(request, response) {
            if (xhr) {
                xhr.abort();
            }

            request = $.trim(request);
            if (request && request.length > 2) {
                xhr = $.get(self.url_module + '/search&term=' + encodeURIComponent(request),
                    function(json) {
                        response(json);
                    }, 'json');
            } else {
                response([]);
            }
        },
        'select': function(item) {
            el.val(item.name);
            self.setFias(item.value);
            self.cities_popup.modal('hide');
        }
    });
    el.siblings('ul.dropdown-menu').css({'maxHeight': 300, 'overflowY': 'auto', 'overflowX': 'hidden'});
};

Prmn.CityManager.prototype.setFias = function(id) {
    $.get(this.url_module + '/save&fias_id=' + id,
        function(json) {
            if (json.success) {
                location.reload();
            }
        },
        'json'
    );
};

Prmn.CityManager.prototype.getHttpHost = function() {
    if (!Prmn.CityManager.host) {
        Prmn.CityManager.host = location.protocol + '//' + location.host + '/';

        if (this.options.base_path === 'auto') {
            var base = $('base').attr('href'), matches;
            if (base && (matches = base.match(/^http(?:s)?:\/\/[^/]*\/(.*)/))) {
                Prmn.CityManager.host += matches[1];
            }
        } else if (this.options.base_path) {
            Prmn.CityManager.host += this.options.base_path;
        }
    }

    return Prmn.CityManager.host;
};

Prmn.CityManager.prototype.confirmShown = function() {
    if (!this.confirm_shown) {
        this.confirm_shown = true;
        $.get(this.url_module + '/confirmshown');
    }
};

/**
 * CitySwitcher
 * @constructor
 */
Prmn.CitySwitcher = function(el) {
    this.$element = el;
    this.loaded = !el.is(':empty');
    this.confirm = el.find('.prmn-cmngr__confirm');
    var self = this;

    el.on('click', '.prmn-cmngr__city', function() {
        Prmn.city_manager.showCitiesPopup();
    });

    el.on('click', '.prmn-cmngr__confirm-btn', function() {
        if ($(this).data('value') === 'no') {
            Prmn.city_manager.showCitiesPopup();
        } else if ($(this).data('redirect')) {
            location.href = $(this).data('redirect');
        }
        self.hideConfirm();
    });

    this.showConfirm();
};

Prmn.CitySwitcher.prototype.setContent = function(html) {
    if (!this.loaded) {
        html = $(html);
        this.$element.html(html);
        this.loaded = true;
        this.confirm = this.$element.find('.prmn-cmngr__confirm');
        this.showConfirm();
    }
};

Prmn.CitySwitcher.prototype.showConfirm = function() {
    if (this.confirm.length) {
        Prmn.city_manager.confirmShown();

        if (!(this.$element.data('confirm') === false)) {
            this.confirm.show();
        } else {
            this.confirm.remove();
        }
    }
};

Prmn.CitySwitcher.prototype.hideConfirm = function() {
    this.confirm.hide();
};

/**
 * CityMessage
 * @constructor
 */
Prmn.CityMessage = function(el) {
    this.$element = el;
    this.key = el.data('key');
    this.default = el.data('default');
    this.$element.removeAttr('data-key').removeAttr('data-default');
    this.found = false;
};

Prmn.CityMessage.prototype.setContent = function(html) {
    this.$element.html(html);
    this.found = true;
};

Prmn.CityMessage.prototype.setDefault = function() {
    if (!this.found) {
        this.$element.html(this.default);
    }
};

$(function() {
    var switchers = $('.prmn-cmngr'), messages = $('.prmn-cmngr-message');
    if (switchers.length || messages.length) {
        Prmn.city_manager = new Prmn.CityManager();
        switchers.each(function() {
            Prmn.city_manager.addSwitcher($(this));
        });
        messages.each(function() {
            Prmn.city_manager.addMessage($(this));
        });
        Prmn.city_manager.loadData();
    }
});
