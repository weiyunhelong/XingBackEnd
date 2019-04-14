<?PHP
namespace app\models;

use yii\base\Model;
use yii\web\UploadedFile;

/**
 * UploadForm is the model behind the upload form.
 */
class Test extends Model
{

    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            [['content'], 'required'],
            [['content'], 'string'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'content' => 'content'
        ];
    }
}
?>