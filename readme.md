# Purchased Files API Endpoint
Allows calling the EDD JSON API with a API Key and Token to get a list of files the user associated with the credentials has access to

## Usage
For EDD Puchases

`http://edd.dev/edd-api/my-files?key=<api key>&token=<api token>`

For use with Restrict Content Pro - EDD Member Downloads

`http://edd.dev/edd-api/member-downloads?key=<api key>&token=<api token>`

## Return
```
{
    "files": {
        "10": {
            "name": "A Sample Digital Download",
            "files": [
                {
                    "file_name": "Download Files",
                    "download_url": "http://edd.dev/index.php?eddfile=2073%3A10%3A0%3A0&ttl=1501874375&file=0&token=534022eba1159c2e35a41f60aa9dc813"
                }
            ]
        },
        "16": {
            "name": "Sample Product",
            "files": [
                {
                    "file_name": "Files",
                    "download_url": "http://edd.dev/index.php?eddfile=2076%3A16%3A0%3A2&ttl=1501874375&file=0&token=34abc1eeb6599321b0905310bed92d86"
                }
            ]
        },
        "67": {
            "name": "Another Sample Product",
            "files": [
                {
                    "file_name": "Files",
                    "download_url": "http://edd.dev/index.php?eddfile=1954%3A67%3A0%3A1&ttl=1501874376&file=0&token=e57be57a07f3a720db6ab9354eb322d2"
                }
            ]
        },
        "68": {
            "name": "One More Sample Product",
            "files": [
                {
                    "file_name": "Files",
                    "download_url": "http://edd.dev/index.php?eddfile=1949%3A68%3A0%3A0&ttl=1501874376&file=0&token=f149600e992b12510d0a042011e0ac04"
                }
            ]
        },
        "514": {
            "name": "A Music Album",
            "files": {
                "1": {
                    "file_name": "Screen Shot 2017-05-26 at 4.59.24 PM-300x191",
                    "download_url": "http://edd.dev/index.php?eddfile=1940%3A514%3A1%3A2&ttl=1501874376&file=1&token=d18a90c9fd99103c966dea341346bd96"
                }
            }
        },
        "1906": {
            "name": "Greyscalegorilla Drive",
            "files": {
                "1": {
                    "file_name": "top-ten-pixar-monster (1)-300x225",
                    "download_url": "http://edd.dev/index.php?eddfile=2070%3A1906%3A1%3A1&ttl=1501874376&file=1&token=5ce095e5eb574e29f6f9b0557353f865"
                },
                "2": {
                    "file_name": "cars_pixar-300x194",
                    "download_url": "http://edd.dev/index.php?eddfile=2082%3A1906%3A2%3A2&ttl=1501874375&file=2&token=05c7b7915bc556ceb5a5c7664944b39b"
                },
                "4": {
                    "file_name": "Free-300x300",
                    "download_url": "http://edd.dev/index.php?eddfile=2070%3A1906%3A4%3A1&ttl=1501874376&file=4&token=38eb814add084b51b61474ecb553f927"
                }
            }
        }
    },
    "link_expiration": 1501874376,
    "request_speed": 0.083628177642822
}
```