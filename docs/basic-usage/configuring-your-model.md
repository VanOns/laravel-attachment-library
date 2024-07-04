Accepting attachments for your object requires the use of the trait: `HasAttachments`.

Your model should look like:
```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use VanOns\LaravelAttachmentLibrary\Concerns\HasAttachments;

class ModelName extends Model
{
    use HasAttachments;
    
    // ...
}
```

#### Attach attachments
You can attach existing attachments to your object in the following way:

```php
// Retrieve attachment.
$attachment = Attachment::find($id);

// Retrieve your model.
$myModel = ModelName::find($id);

// Link attachment to your model.
$myModel->attachments()->attach($attachment);
```

#### Detach attachments
You can detach existing attachments to your object in the following way:

```php
// Retrieve attachment.
$attachment = Attachment::find($id);

// Retrieve your model.
$myModel = ModelName::find($id);

// Detach attachment from your model.
$myModel->attachments()->detach($attachment);
```
