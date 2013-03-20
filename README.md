# Pushy SDK (PHP)

Pushy is a Publish/Subscribe system for realtime web applications. 
This is the SDK to use in your application to trigger events from server-side.

## Installation

Add a require clause ```spokela/pushy-sdk-php``` into your ```composer.json``` file to get started.

## Usage

Just instanciate the ```Pushy\Pushy``` object and enjoy your realtime web app!

```php
use Pushy\Pushy;

$pushy = new Pushy("http://localhost:8234", $secretKey = "secretKey", $timeout = 3);
```

### Trigger events

Triggering events is pretty straightforward:

```php
try {
    $pushy->trigger("<channel-name>", "<event-name>", array(/* data*/));
} catch(\Pushy\Exception $exception) {
    // handle eventual exception (or not)
}
```

This method always throws a ```\Pushy\Exception``` if an error occurs and returns ```false``` if the channel where you're dispatching the event don't exists (= zero subscribers).

### Authentication

This class can also be used as an authentication mechanism. When a client tries to subscribe to a private channel, it should request your 
application who is supposed to return an authentication token for this user. 

```php
$authData = $pushy->authorize($channelName, $connectionId, $allowed = true);
```

Here is an example for a simple auth handler ```auth.php``` that works with the [official client](https://github.com/spokela/pushy-client):

```php
$authData = $pushy->authorize($_POST['channel'], $_POST['connection_id'], true);

header('Content-type: application/json');
echo(json_encode($authData));
```

### Presence data

Additionnally, you can use the same method as above to specify presence-data for this user:

```php
$presenceData = array(
    'username'  => $_SESSION['username'],
    'avatar'    => "http://gravatar.com/some/avatar/url.png"
);

$authData = $pushy->authorize($_POST['channel'], $_POST['connection_id'], true, $presenceData);

header('Content-type: application/json');
echo(json_encode($authData));
```


## LICENSE

This software is licensed under the MIT License. Please refer to the LICENSE file for more details.

```
THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.
```