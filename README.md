# Formidable Submission Limiter WordPress Plugin

## Description

The Formidable Submission Limiter is a WordPress plugin that allows you to limit the rate of submissions on Formidable Forms based on the user's IP address. It helps prevent excessive form submissions within a defined time interval, reducing the risk of spammy or abusive submissions.

## Features

- Limits the rate of form submissions per IP address.
- Customizable submission rate limit and time interval.
- Displays a user-friendly error message when the submission limit is exceeded.
- Easy integration with Formidable Forms.

## Installation

1. Download the plugin ZIP file.
2. Log in to your WordPress admin panel.
3. Navigate to "Plugins" > "Add New."
4. Click the "Upload Plugin" button at the top of the page.
5. Choose the downloaded ZIP file and click "Install Now."
6. Activate the plugin.

## Usage

Once the plugin is activated and configured, it will automatically start limiting form submissions based on the defined rate and time interval. If a user attempts to submit a form too quickly and exceeds the submission limit, they will see the specified error message.

## Error Message

The default error message displayed when the submission limit is exceeded is:
"Sorry, we are receiving a high volume at this time. Please try again in a minute or reach out to us by phone."

You can customize this message by modifying the `formidable_submission_limit_message` setting in the plugin code.

The default limit is 2 requests per minute per IP address.

## Configuration

The plugin's behavior can be adjusted by modifying the following settings:

- `formidable_submission_rate_limit`: Defines the maximum number of submissions allowed from a single IP address within the time frame specified by `formidable_submission_time_limit`. Default is 2 submissions.
- `formidable_submission_time_limit`: Specifies the time frame, in seconds, in which submissions are counted for rate limiting. Default is 60 seconds.
- `formidable_submission_limit_message`: The error message displayed when the submission rate limit is exceeded. Default message is "Sorry, we are receiving a high volume at this time. Please try again in a minute or reach out to us by phone."
- `formidable_submission_data_size`: Sets the maximum size, in bytes, for storing submission data. Default is 524288 bytes (0.5 MB).
- `formidable_submission_transient_expiry`: Defines how long, in seconds, the transient data is stored before expiration. Default is 3600 seconds (1 hour).
- `formidable_submission_max_submissions`: The maximum total number of submissions allowed from a single IP address. Default is 100 submissions.

## License
This project is licensed under the MIT License - see the LICENSE file for details.

## Contributing
If you have suggestions or improvements, feel free to create an issue or submit a pull request.