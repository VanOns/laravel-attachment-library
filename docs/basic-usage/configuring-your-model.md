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
// Or, to add the attachment to a specific collection:
$myModel->attachments()->attach($attachment, ['collection' => 'collection_name_here']);
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

## Retrieve attachments
You can retrieve the attachments for your model in the following way:

```php
// Retrieve all attachments
$model->attachments()->get();

// Retrieve attachments for a specific collection
$model->attachments()->wherePivot('collection', 'collection_name_here')->get();
```

To make querying attachments in a specific collection easier, you can add a separate relationship method to your model:

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use VanOns\LaravelAttachmentLibrary\Concerns\HasAttachments;

class ModelName extends Model
{
    use HasAttachments;

    public function gallery(): MorphToMany
    {
        return $this->attachmentCollection('gallery');
    }
}
```

This allows you to retrieve the attachments in the `gallery` collection like so:

```php
$model->gallery()->get();
```
