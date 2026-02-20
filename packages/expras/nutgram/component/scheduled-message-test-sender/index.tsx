import {Typography, Grid, TextField} from "@mui/material";
import {useEffect, useState, useMemo} from 'react';
import {useRecordContext, useNotify, useDataProvider} from 'react-admin';
import LoadingButton from '@mui/lab/LoadingButton';
import SendIcon from "@mui/icons-material/Send";
import Autocomplete from '@mui/material/Autocomplete';
import {debounce} from '@mui/material/utils';
import UserIcon from '@mui/icons-material/Person';

interface ScheduledMessageTestSenderProps {
    botUsersUrl?: string;
    sendTestMessageUrl?: string;
}

export const ScheduledMessageTestSender = ({
    botUsersUrl = 'expras-nutgram-bot-users',
    sendTestMessageUrl = 'expras-nutgram-bot-scheduledmessage/send-test-message'
}: ScheduledMessageTestSenderProps = {}) => {
    const [submitting, setSubmitting] = useState<boolean>(false)
    const notify = useNotify();
    const dataProvider = useDataProvider();

    const [value, setValue] = useState(null);
    const [inputValue, setInputValue] = useState('');
    const [options, setOptions] = useState([]);
    const record = useRecordContext();

    const fetchBotUsers = useMemo(
        () =>
            debounce((inputValue, callback) => {
                dataProvider.fetch(botUsersUrl + '?' + JSON.stringify({
                    _filter: {__query: {operator: "like", value: inputValue}}
                }), {
                    method: 'GET',

                })
                    .then(r => r.json())
                    .then((results) => {
                        callback(results);
                    })
            }, 400),
        [botUsersUrl],
    );

    useEffect(() => {
        let active = true;

        if (inputValue === '') {
            setOptions([]);
            return undefined;
        }

        fetchBotUsers(inputValue, (results) => {
            if (active) {
                let newOptions = [];

                if (value) {
                    newOptions = [value];
                }

                if (results) {
                    newOptions = [...newOptions, ...results];
                }

                setOptions(newOptions);
            }
        });


        return () => {
            active = false;
        };
    }, [value, inputValue, fetchBotUsers]);

    const sendMessage = () => {
        setSubmitting(true);
        dataProvider.fetch(sendTestMessageUrl, {
            method: 'post',
            body: JSON.stringify({
                message: record.id,
                user: value.id
            }),
            headers: {
                'Content-Type': 'application/json'
            }
        })
            .then(r => r.json())
            .then(({success, message}) => {
                if (success) {
                    notify(`Сообщение успешно отправлено`, {type: 'success'});
                } else {
                    notify(message || 'Произошла неизвестная ошибка', {type: 'error'});
                }

            })
            .catch(e => {
                notify(e.message || 'Произошла неизвестная ошибка', {type: 'error'});
            })
            .finally(() => {
                setSubmitting(false);
            })

    }

    return <div>
        <div>
            <Autocomplete
                sx={{width: 300}}
                getOptionLabel={(option) =>
                    option.username || 'id:' + option.id
                }
                filterOptions={(x) => x}
                options={options}
                autoComplete
                includeInputInList
                filterSelectedOptions
                value={value}
                noOptionsText="Пользователь не найден"
                onChange={(event, newValue) => {
                    setOptions(newValue ? [newValue, ...options] : options);
                    setValue(newValue);
                }}
                onInputChange={(event, newInputValue) => {
                    setInputValue(newInputValue);
                }}
                renderInput={(params) => (
                    <TextField {...params} label="Выберите пользователей" fullWidth/>
                )}
                renderOption={(props, {username, firstName, lastName}) => {
                    const {key, ...optionProps} = props;
                    return (
                        <li key={key} {...optionProps}>
                            <Grid container sx={{alignItems: 'center'}}>
                                <Grid item sx={{display: 'flex', width: 44}}>
                                    <UserIcon sx={{color: 'text.secondary'}}/>
                                </Grid>
                                <Grid item sx={{width: 'calc(100% - 44px)', wordWrap: 'break-word'}}>
                                    {username}
                                    <Typography variant="body2" sx={{color: 'text.secondary'}}>
                                        {firstName} {lastName}
                                    </Typography>
                                </Grid>
                            </Grid>
                        </li>
                    );
                }}
            />
        </div>
        <div style={{
            display: 'flex',
            marginTop: '1.5rem',
            justifyContent: 'flex-end'
        }}>
            <LoadingButton disabled={!value}
                           loading={submitting}
                           onClick={sendMessage}>
                Отправить
                <SendIcon
                    sx={{marginLeft: '0.5rem'}}
                    color="primary"/>
            </LoadingButton>
        </div>
    </div>
}