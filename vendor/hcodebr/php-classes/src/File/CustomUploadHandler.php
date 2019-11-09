<?
namespace Hcode\File;

use Hcode\File\UploadHandler;
use Hcode\DB\Sql;

class CustomUploadHandler extends UploadHandler {

    protected function initialize() {
    	$this->db = new \mysqli(
    		Sql::HOSTNAME,
    		Sql::USERNAME,
    		Sql::PASSWORD,
    		Sql::DBNAME
    	);
        parent::initialize();
        $this->db->close();
    }

    protected function handle_form_data($file, $index) {
    	$file->title = @$_REQUEST['title'][$index];
    	$file->description = @$_REQUEST['description'][$index];
    }

    protected function handle_file_upload($uploaded_file, $name, $size, $type, $error,
            $index = null, $content_range = null) {
        $file = parent::handle_file_upload(
        	$uploaded_file, $name, $size, $type, $error, $index, $content_range
        );
        if (empty($file->error)) {
			$sql = 'INSERT INTO `'.$this->options['db_table']
				.'` (`name`, `size`, `type`, `title`, `description`, `thumbnailUrl`,`deleteType`, `deleteUrl`,`idvinculo`,`model`)'
				.' VALUES (?, ?, ?, ?, ?, ?,?,?,?, ?)';
	        $query = $this->db->prepare($sql);
            
	        $query->bind_param(
                'sissssssis',
	        	$file->name,
	        	$file->size,
	        	$file->type,
	        	$file->title,
	        	$file->description,
	        	$file->thumbnailUrl,
	        	$file->deleteType,
	        	$file->deleteUrl,
                $this->id,
                $this->model
	        );
	        $query->execute();
	        $file->id = $this->db->insert_id;
        }
        return $file;
    }

    protected function set_additional_file_properties($file) {
        parent::set_additional_file_properties($file);
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        	$sql = 'SELECT `id`, `type`, `title`, `description` FROM `'
        		.$this->options['db_table'].'` WHERE `name`=?';
        	$query = $this->db->prepare($sql);
 	        $query->bind_param('s', $file->name);
	        $query->execute();
	        $query->bind_result(
	        	$id,
	        	$type,
	        	$title,
	        	$description
	        );
	        while ($query->fetch()) {
	        	$file->id = $id;
        		$file->type = $type;
        		$file->title = $title;
        		$file->description = $description;
        		
    		}
        }
    }

    public function delete($print_response = true) {
        $response = parent::delete(false);
        foreach ($response as $name => $deleted) {
        	if ($deleted) {
	        	$sql = 'DELETE FROM `'
	        		.$this->options['db_table'].'` WHERE `name`=?';
	        	$query = $this->db->prepare($sql);
	 	        $query->bind_param('s', $name);
		        $query->execute();
        	}
        } 
        return $this->generate_response($response, $print_response);
    }

}

?>

