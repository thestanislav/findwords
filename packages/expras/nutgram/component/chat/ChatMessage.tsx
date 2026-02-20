import React from 'react';
import {Avatar, Typography, Grid2 as Grid, GridProps, AvatarProps, TypographyProps,} from '@mui/material';
import cx from "clsx";
import {Styles, ThemeOfStyles, WithStylesOptions} from "@mui/styles";
import {TelegramMessageObject} from "./index";
import {styled} from '@mui/material/styles';

// @ts-ignore
import ImageField from '@expras/react-admin/field/ImageField';

export type ChatMsgMessage = {
    text: string,
    object?: {
        message?: TelegramMessageObject
    },
    date: Date
}
export type ChatMsgProps = {
    style: Styles<any, any>,
    options?: WithStylesOptions<ThemeOfStyles<Styles<any, any>>>
} & {
    avatar?: string,
    initials: string,
    messages: ChatMsgMessage[],
    side?: 'left' | 'right',
    GridContainerProps?: GridProps,
    GridItemProps?: GridProps,
    AvatarProps?: AvatarProps,
    getTypographyProps?: (msg: ChatMsgMessage, index: number, props: ChatMsgProps) => TypographyProps,
    key?: number,
    className: string
};


const ChatMessage = ((props: ChatMsgProps) => {
        const {

            avatar = null,
            initials = null,
            messages = [],
            side = "left",
            GridContainerProps = {},
            GridItemProps = {},
            AvatarProps = {},
            getTypographyProps = (): TypographyProps => ({}),
            key,
            className
        } = props;


        function stringToColor(string: string) {
            let hash = 0;
            let i;

            /* eslint-disable no-bitwise */
            for (i = 0; i < string.length; i += 1) {
                hash = string.charCodeAt(i) + ((hash << 5) - hash);
            }

            let color = '#';

            for (i = 0; i < 3; i += 1) {
                const value = (hash >> (i * 8)) & 0xff;
                color += `00${value.toString(16)}`.slice(-2);
            }
            /* eslint-enable no-bitwise */

            return color;
        }

        function stringAvatar(name: string) {
            return {
                sx: {
                    bgcolor: stringToColor(name),
                },
                children: `${name.split(' ')[0][0]}${name.split(' ')[1][0]}`,
            };
        }


        return (
                <Grid
                    key={key}
                    container
                    spacing={1}
                    {...GridContainerProps}
                    className={cx(`${side}Messages`, GridContainerProps.className, className)}
                >
                    {side === "left" && (
                        <Grid size={1} {...GridItemProps}>
                            <Avatar
                                title={initials}
                                {...avatar ? {src: avatar} : stringAvatar(initials)}
                                {...AvatarProps}
                                className={cx('avatar', AvatarProps.className)}
                            />
                        </Grid>
                    )}
                    <Grid size={11}>
                        {messages.map(({text, date, object}: ChatMsgMessage, i) => {
                            const message = object?.message || object || {};
                            const {caption, video, audio, voice, photo, document: docAttach} = message || {};
                            const typographyProps = getTypographyProps({text, date}, i, props);
                            return (
                                // eslint-disable-next-line react/no-array-index-key
                                <div key={i} className={`${side}Row`}>
                                    <Typography
                                        component="div"
                                        align={side}
                                        {...typographyProps}
                                        className={cx(
                                            'msg',
                                            side,
                                            i === 0 ? `${side}First` : (i === messages.length - 1 ? `${side}Last` : ''),
                                            typographyProps.className
                                        )}
                                    >
                                        {!caption &&text && text.split(/[\r\n]+/).map((v, i) => <p key={i} dangerouslySetInnerHTML={{__html: v}}></p>)}
                                        {caption && caption.split(/[\r\n]+/).map((v, i) => <p key={i} dangerouslySetInnerHTML={{__html: v}}></p>)}
                                        {video && <video controls width={video.width} height={video.height}>
                                            <source src={`/.admin/expras-nutgram-bot/file?fileId=${video.file_id}`} type={video.mime_type}/>
                                        </video>}
                                        {audio && <audio controls>
                                            <source src={`/.admin/expras-nutgram-bot/file?fileId=${audio.file_id}`} type={audio.mime_type}/>
                                        </audio>}
                                        {voice && <audio controls>
                                            <source src={`/.admin/expras-nutgram-bot/file?fileId=${voice.file_id}`} type={voice.mime_type}/>
                                        </audio>}
                                        {docAttach && ((d) => {
                                                if (d.mime_type.match(/image/i)) {
                                                    return <ImageField native={true}
                                                    record={{
                                                        'path': `/.admin/expras-nutgram-bot/file?fileId=${d.file_id}`
                                                    }}
                                                    source="path"
                                                    src="path"/>
                                                } else if (d.mime_type.match(/video/i)) {
                                                    return <a href={`/.admin/expras-nutgram-bot/file?fileId=${d.file_id}`}><video controls width={d.width} height={d.height}>
                                                        <source src={`/.admin/expras-nutgram-bot/file?fileId=${d.file_id}`} type={d.mime_type}/>
                                                    </video></a>
                                                } else if (d.mime_type.match(/audio/i)) {
                                                    return <a href={`/.admin/expras-nutgram-bot/file?fileId=${d.file_id}`}><audio controls>
                                                        <source src={`/.admin/expras-nutgram-bot/file?fileId=${d.file_id}`} type={d.mime_type}/>
                                                    </audio></a>
                                                } else {
                                                    return <a href={`/.admin/expras-nutgram-bot/file?fileId=${d.file_id}`}>{d.file_name}</a>
                                                }
                                            })(docAttach)
                                        }
                                        {Array.isArray(photo) && photo.length > 0 &&
                                            <ImageField native={true}
                                                        record={{
                                                            'path': `/.admin/expras-nutgram-bot/file?fileId=${photo.sort((a, b) => b.width - a.width)[0].file_id}`
                                                        }}
                                                        source="path"
                                                        src="path"/>}

                                        <span className="date">
                                        {date.toLocaleString('ru-RU')}
                                    </span>
                                    </Typography>


                                </div>
                            );
                        })}
                    </Grid>
                    {side === "right" && (
                        <Grid size={1} {...GridItemProps}>
                            <Avatar
                                title={initials}
                                {...avatar ? {src: avatar} : stringAvatar(initials)}
                                {...AvatarProps}
                                className={cx('avatar', AvatarProps.className)}
                            />
                        </Grid>
                    )}
                </Grid>
        );
    }
);

export default styled(ChatMessage)(({theme}) => {
    const radius = theme.spacing(2.5);
    const size = theme.spacing(8);
    const isDarkMode = theme.palette.mode === 'dark';

    return {
        '& .avatar': {
            width: size,
            height: size
        },
        '& .leftRow': {
            textAlign: "left"
        },
        '& .rightRow': {
            display: 'flex',
            justifyContent: 'flex-end'
        },
        '&.leftMessages': {
            '& .MuiAvatar-root': {
                marginLeft: 'auto'
            }
        },
        '& .msg': {
            padding: theme.spacing(1, 2),
            borderRadius: 4,
            marginBottom: 4,
            display: "inline-block",
            wordBreak: "break-word",
            fontFamily:
                '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol"',
            fontSize: "14px"
        },
        '& .left': {
            borderTopRightRadius: radius,
            borderBottomRightRadius: radius,
            backgroundColor: isDarkMode ? theme.palette.grey[800] : theme.palette.grey[200],
            color: isDarkMode ? theme.palette.grey[200] : theme.palette.text.primary,
        },
        '& .right': {
            borderTopLeftRadius: radius,
            borderBottomLeftRadius: radius,
            backgroundColor: isDarkMode ? theme.palette.grey[700] : theme.palette.grey[300],
            color: isDarkMode ? theme.palette.grey[200] : theme.palette.grey[900],
            maxWidth: '60%',
            marginLeft: 'auto',
            textAlign: "left",
            flex: '0 1 auto'
        },
        '& .leftFirst': {
            borderTopLeftRadius: radius
        },
        '& .leftLast': {
            borderBottomLeftRadius: radius
        },
        '& .rightFirst': {
            borderTopRightRadius: radius
        },
        '& .rightLast': {
            borderBottomRightRadius: radius
        },
        '& .date': {
            fontSize: theme.spacing(1.2),
            opacity: 0.7,
            display: 'flex',
            justifyContent: 'flex-end',
            color: isDarkMode ? theme.palette.grey[500] : theme.palette.text.secondary
        }
    }
});