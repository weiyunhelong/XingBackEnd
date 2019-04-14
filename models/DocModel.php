<?PHP
namespace app\models;

use yii\base\Model;
use yii\web\UploadedFile;

/**
 * UploadForm is the model behind the upload form.
 */
class DocModel extends Model
{
    public $file;
    
    public $aid;
    public $tid;
    public $uid;
    public $maxnum;
    public $uploadnum;

    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            [['aid','tid','uid','maxnum','uploadnum'], 'required'],
            [['aid','tid','uid','maxnum','uploadnum'], 'integer'],
            [['aid','tid','uid','maxnum','uploadnum'], 'safe']
        ];
    }
}
?>