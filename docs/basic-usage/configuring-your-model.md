# Configuring your model

Accepting attachments for your model requires the use of the trait: `HasAttachments`. Your model should look like the following:

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

## Attach attachments

You can attach existing attachments to your object in the following way:

```php
// Retrieve attachment.
$attachment = \VanOns\LaravelAttachmentLibrary\Models\Attachment::find($attachmentId);

// Retrieve your model.
$myModel = ModelName::find($modelId);

// Link attachment to your model.
$myModel->attachments()->attach($attachment);
```

## Detach attachments

You can detach existing attachments from your object in the following way:

```php
// Retrieve attachment.
$attachment = \VanOns\LaravelAttachmentLibrary\Models\Attachment::find($attachmentId);

// Retrieve your model.
$myModel = ModelName::find($modelId);

// Detach attachment from your model.
$myModel->attachments()->detach($attachment);
```
