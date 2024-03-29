<?php
class Form_render
{
	private $__Form_elements;

	function __construct($form_elements)
	{
		$this->__Form_elements = $form_elements;
	}

	public function Render_form()
	{
        $onchange = "";
		$str = "";
		$elements_array_count = count($this->__Form_elements);
		//echo $elements_array_count;
		if($elements_array_count > 0)
		{
			foreach($this->__Form_elements as $elements)
			{
				$attributes = null;
				if(is_array($elements['attributes']))
				{
					$attributes = implode(" ", $elements['attributes']);
				}

				switch ($elements['type'])
				{
					case "textbox":
						$str .= "
							<label for=\"".$elements['name']."\">\n
								<span>".$elements['title']."</span>\n
								<input type=\"text\" name=\"".$elements['name']."\" id=\"".$elements['name']."\" class=\"".$elements['class']."\" value=\"".$elements['value']."\" style=\"".$elements['style']."\" ".$attributes.">\n
							</label>\n
						";
					break;
					case "password":
						$str .= "
							<label for=\"".$elements['name']."\">\n
								<span>".$elements['title']."</span>\n
								<input type=\"".$elements['type']."\" name=\"".$elements['name']."\" id=\"".$elements['name']."\" value=\"".$elements['value']."\" style=\"".$elements['style']."\" ".$attributes." pattern=".$elements['pattern'].">\n
								<div id=\"message\">
								<h3>Password must contain the following:</h3>
								<p id=\"letter\" class=\"invalid\">A <b>lowercase</b> letter</p>
								<p id=\"capital\" class=\"invalid\">A <b>capital (uppercase)</b> letter</p>
								<p id=\"number\" class=\"invalid\">A <b>number</b></p>
								<p id=\"length\" class=\"invalid\">Minimum <b>8 characters</b></p>
							  </div>
							</label>\n
						";
					break;
					
					case "date":
						$str .= "
							<label for=\"".$elements['name']."\">\n
								<span>".$elements['title']."</span>\n
								<input type=\"date\" name=\"".$elements['name']."\" id=\"".$elements['name']."\" class=\"".$elements['class']."\" value=\"".$elements['value']."\" style=\"".$elements['style']."\" ".$attributes.">\n
							</label>\n
						";
					break;
					case "textarea":
						$str .= "
							<label for=\"".$elements['name']."\">\n
								<span>".$elements['title']."</span>\n
								<textarea name=\"".$elements['name']."\" id=\"".$elements['name']."\" ".$attributes.">".$elements['value']."</textarea>\n
							</label>\n
						";
					break;
					case "file":
						$str .= "
							<label for=\"".$elements['name']."\">\n
								<span>".$elements['title']."</span>\n
								<input type=\"".$elements['type']."\" name=\"".$elements['name']."\" id=\"".$elements['name']."\" value=\"".$elements['value']."\" class=\"control-label ".$elements['class']."\" style=\"".$elements['style']."\">\n
							</label>\n
						";

						if(strlen($elements['value']) > 0)
						{
							$file_info_array = pathinfo($elements['value']);
							$ext = strtolower($file_info_array['extension']);
							
							//allowed exts
							$ext_array = array("jpg", "gif", "png");
							
							if(in_array($ext, $ext_array))
							{
								$image_width = 300;
								@$file_size = getimagesize($_SERVER['DOCUMENT_ROOT'].$elements['data'].'/'.$elements['value']);
								if($file_size[0] > 300)
								{
									$image_width = 300;
								}
								else{
									$image_width = $file_size[0];
								}
							
								$str .= "
									<label for=\"\">\n
										<span>".$elements['title']." Preview:</span>\n
										<img src=\"".$elements['data'].'/'.$elements['value']."\" id=\"image_preview_".$elements['name']."\" width=\"".$image_width."\">\n
									</label>\n
								";
							}
							else
							{
								$str .= "
									<label for=\"label-file\">\n
										<span>".$elements['title']." Preview:</span>\n
										<a href=\"".$elements['data']."/".$elements['value']."\" class=\"file-download\" id=\"file_preview\" target=\"_blank\"><i style=\"font-size:25px!important;\" class=\"fa fa-download\" aria-hidden=\"true\"></i>  ".$elements['value']."</a>\n
									</label>\n
								";
							}
						}
					break;
					
					case "checkbox":
						//check the data element of the array as that is where the drop downlist data will be
						$data_array_count = count($elements['data']);
						//echo $data_array_count;
						$opt = "";

						//create the option list
						foreach($elements['data'] as $data)
						{
							$values = array_values($data);
							if($elements['value'] == $values[0])
							{
								$opt .= " checked";
							}
							$opt .= "<input type=\"checkbox\" name=\"".$elements['name']."\" id=\"".$elements['name']."\" value=\"".$values[0]."\" class=\"".$elements['class']."\" style=\"".$elements['style']."\" ".$checked." ".$attributes.">".$values[1].">\n";
							$checked = "";
						}

						$str .= "
							<label for=\"".$elements['name']."\" class=\"checkbox\">\n
								<span>".$elements['title']."</span>\n
								".$opt."\n
							</label>\n";
					break;
					case "radiobuttons":
						//check the data element of the array as that is where the drop downlist data will be
						$data_array_count = count($elements['data']);
						//echo $data_array_count;
						$opt = "";

						//create the option list
						foreach($elements['data'] as $data)
						{
							$values = array_values($data);
							if($elements['value'] == $values[0])
							{
								$opt .= " checked";
							}
							$opt .= "
							<label for=\"radio-option-1\" class=\"radio\">\n
								<input type=\"radio\" name=\"".$elements['name']."\" id=\"".$elements['name']."\" value=\"".$values[0]."\" class=\"".$elements['class']."\" style=\"".$elements['style']."\" ".$checked.">\n
								<span>".$values[1]."</span>\n
							</label>\n";
							$checked = "";
						}

						$str .= "
							<label for=\"".$elements['name']."\" class=\"radio-title\">\n
								<span>".$elements['title']."</span>\n
								".$opt."\n
							</label>\n
						";
					break;
					default:
					break;
				}
			}
		}
		return $str;
	}
	
}