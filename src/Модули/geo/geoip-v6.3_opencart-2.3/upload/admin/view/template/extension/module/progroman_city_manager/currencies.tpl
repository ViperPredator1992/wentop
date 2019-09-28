<div class="for-general-form row">
  <div class="col-lg-3 col-md-4 col-sm-4">
    <div class="form-group">
      <label class="control-label">
        <input name="progroman_cm_setting[enable_switch_currency]" value="1" type="checkbox"
          <?= !empty($settings['enable_switch_currency']) ? ' checked="checked"' : ''; ?>">
          <?= $entry_sub_enabled; ?>
      </label>
    </div>
  </div>
</div>

<form action="<?= $action_currencies; ?>" class="main-form" data-submit="saveCurrencies">
  <table id="currencies" class="table table-striped table-bordered">
    <thead>
    <tr>
      <td>
        <div class="row">
          <div class="col-lg-2 col-md-3 col-sm-4 col-xs-5">
              <?= $entry_country; ?>
          </div>
          <div class="col-lg-2 col-md-3 col-sm-4 col-xs-5">
              <?= $entry_currency; ?>
          </div>
        </div>
      </td>
    </tr>
    </thead>
    <tbody>
    <?php $currency_row = 0; ?>
    <?php foreach ($cm_currencies as $cm_currency) { ?>
      <tr id="currency-row<?= $currency_row; ?>">
        <td>
          <div class="row">
            <div class="col-lg-2 col-md-3 col-sm-4 col-xs-5">
              <select name="currencies[<?= $currency_row; ?>][country_id]" class="form-control">
                <option value="0"><?= $text_none; ?></option>
                  <?php foreach ($countries as $country) { ?>
                    <option value="<?= $country['country_id']; ?>"
                        <?= $country['country_id'] == $cm_currency['country_id'] ? 'selected' : ''; ?>>
                        <?= $country['name']; ?>
                    </option>
                  <?php } ?>
              </select>
            </div>
            <div class="col-lg-2 col-md-3 col-sm-4 col-xs-5">
              <select name="currencies[<?= $currency_row; ?>][code]" class="form-control">
                <option value="0"><?= $text_none; ?></option>
                  <?php foreach ($currencies as $currency) { ?>
                    <option value="<?= $currency['code']; ?>"
                        <?= $currency['code'] == $cm_currency['code'] ? 'selected' : ''; ?>>
                        <?= $currency['title']; ?>
                    </option>
                  <?php } ?>
              </select>
            </div>
            <div class="col-md-1">
              <a onclick="$('#currency-row<?= $currency_row; ?>').remove();" class="btn btn-danger">
                <i class="fa fa-remove visible-xs"></i>
                <span class="hidden-xs"><?= $button_remove; ?></span>
              </a>
            </div>
          </div>
        </td>
      </tr>
        <?php $currency_row++; ?>
    <?php } ?>
    </tbody>
    <tfoot>
    <tr>
      <th>
        <a class="btn btn-success" onclick="addCurrency();">
            <?= $button_add; ?>
        </a>
      </th>
    </tr>
    </tfoot>
  </table>
</form>

<select id="select-countries" class="hidden">
  <option value="0"><?= $text_none; ?></option>
    <?php foreach ($countries as $country) { ?>
      <option value="<?= $country['country_id']; ?>"><?= $country['name']; ?></option>
    <?php } ?>
</select>
<select id="select-currencies" class="hidden">
  <option value="0"><?= $text_none; ?></option>
    <?php foreach ($currencies as $currency) { ?>
      <option value="<?= $currency['code']; ?>"><?= $currency['title']; ?></option>
    <?php } ?>
</select>

<script type="text/javascript">
    var currency_row = <?= $currency_row; ?>;

    function addCurrency() {
        var html = '<tr id="currency-row' + currency_row + '"><td>';
        html += '<div class="row"><div class="col-lg-2 col-md-3 col-sm-4 col-xs-5">';
        html += '<select name="currencies[' + currency_row + '][country_id]" class="form-control">';
        html += $('#select-countries').html();
        html += '</select>';
        html += '</div><div class="col-lg-2 col-md-3 col-sm-4 col-xs-5">';
        html += '<select name="currencies[' + currency_row + '][code]" class="form-control">';
        html += $('#select-currencies').html();
        html += '</select>';
        html += '</div><div class="col-md-1">';
        html += '<a onclick="$(\'#currency-row' + currency_row + '\').remove();" class="btn btn-danger">';
        html += '<i class="fa fa-remove visible-xs"></i><span class="hidden-xs"><?= $button_remove; ?></span></a>';
        html += '</div></div></td></tr>';

        $('#currencies').find('tbody').append(html);

        currency_row++;
    }

    function saveCurrencies(callback) {
        var form = $('#tab-currencies').find('form');
        form.find('.text-danger').remove();
        $.post(form.attr('action'), form.serialize(),
            function(json) {
                if (json.errors) {
                    for (i in json.errors.country) {
                        $('#currency-row' + i).find('select[name="currencies\[' + i + '\]\[country_id\]"]')
                            .after('<p class="text-danger">' + json.errors.country[i] + '</p>');
                    }
                    for (i in json.errors.code) {
                        $('#currency-row' + i).find('select[name="currencies\[' + i + '\]\[code\]"]')
                            .after('<p class="text-danger">' + json.errors.code[i] + '</p>');
                    }
                    $('#tabs').find('a[href="#tab-currencies"]').tab('show');
                }

                if (callback) {
                    callback.call(this, !json.errors);
                }
            }, 'json');
    }
</script>