<?php

namespace saritasa\yii2\GooglePlacesAutoComplete;

use yii\widgets\InputWidget;
use yii\helpers\Html;


class GooglePlacesAutoComplete extends InputWidget {

    const API_URL = '//maps.googleapis.com/maps/api/js?';

    public $libraries = 'places';

    public $sensor = true;

    public $language = 'en-US';

    public $autocompleteOptions = ['types'=>['geocode'],'componentRestrictions' => ['country' => 'us']];

    /**
     * Renders the widget.
     */
    public function run(){
        $this->registerClientScript();
        if ($this->hasModel()) {
            echo Html::activeTextInput($this->model, $this->attribute, $this->options);
        } else {
            echo Html::textInput($this->name, $this->value, $this->options);
        }
    }

    /**
     * Registers the needed JavaScript.
     */
    public function registerClientScript(){
        $elementId = $this->options['id'];
        $scriptOptions = json_encode($this->autocompleteOptions);

        $view = $this->getView();
        $view->registerJsFile(self::API_URL . http_build_query([
            'libraries' => $this->libraries,
            'sensor' => $this->sensor ? 'true' : 'false',
            'language' => $this->language
        ]));

        $view->registerJs(<<<JS
(function(){
   	  var componentForm = {
	  street_number: 'short_name',
	  route: 'short_name',
	  locality: 'short_name',
	  administrative_area_level_1: 'short_name',
	  postal_code: 'short_name'
	};


	var billFormDetails = {
	  street_number:'billingmaster-st_address1',
	  route:'billingmaster-st_address1',
	  locality:'billingmaster-st_city',
	  administrative_area_level_1:'billingmaster-ch_state',
	  postal_code:'billingmaster-st_zip'
	};

    var input = document.getElementById('{$elementId}');
    var options = {$scriptOptions};

    var autocomplete = new google.maps.places.Autocomplete(input, options);
	autocomplete.addListener('place_changed',function(){
		var place = autocomplete.getPlace();
			 document.getElementById('billingmaster-st_address1').value='';
			  for (var i = 0; i < place.address_components.length; i++) {
				var addressType = place.address_components[i].types[0];
				if (componentForm[addressType]) {
				  var val = place.address_components[i][componentForm[addressType]];
				 
				  if(addressType == 'street_number' || addressType == 'route'){
					   document.getElementById('billingmaster-st_address1').value =  document.getElementById('billingmaster-st_address1').value +' '+ val;
				  }else{
					  document.getElementById(billFormDetails[addressType]).value = val;
				  } 
				  $('#'+billFormDetails[addressType]).trigger('keyup');  
				}
			  }
		  
	});
})();
JS
        , \yii\web\View::POS_READY);
    }
}
